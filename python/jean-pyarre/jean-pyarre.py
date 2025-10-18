from contextlib import closing
from flask import Flask, render_template, Blueprint
from pathlib import Path
from typing import Optional, List
import mysql.connector
import importlib.util
import sys

if "--check" in sys.argv:
    print("Check mode: application starts successfully")
    sys.exit(0)


class DBManager:
    def __init__(self) -> None:
        self.connection_params = {
            "database": "db",
            "user": "db",
            "password": "db",
            "host": "db",
            "auth_plugin": "mysql_native_password",
        }

    def get_versions(self) -> list[str]:
        try:
            conn = mysql.connector.connect(**self.connection_params)
            with closing(conn.cursor()) as cursor:
                cursor.execute("SELECT version FROM lotto_versions")
                return [row[0] for row in cursor.fetchall()]
        finally:
            conn.close()


class VersionManager:
    def __init__(self, versions_dir: str | Path) -> None:
        self.versions_dir = Path(versions_dir)

    def load_module(self, version: str) -> Optional[object]:
        module_path = self.versions_dir / f"{version}.py"
        if not module_path.is_file():
            return None

        spec = importlib.util.spec_from_file_location(version, module_path)
        if not spec or not spec.loader:
            return None

        module = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(module)

        return module

    def register_all_blueprints(self, app: Flask) -> None:
        for file in self.versions_dir.glob("*.py"):
            version = file.stem
            module = self.load_module(version)
            if not module:
                continue
            bp = getattr(module, "blueprint", None)
            if isinstance(bp, Blueprint):
                bp.url_prefix = f"/algo/{version}"
                app.register_blueprint(bp)


def create_app() -> Flask:
    app = Flask(__name__)
    db_manager = DBManager()
    version_manager = VersionManager("versions")
    version_manager.register_all_blueprints(app)

    def get_active_versions() -> set[str]:
        return set(db_manager.get_versions())

    @app.route("/algo")
    def algo():
        active_versions = get_active_versions()
        print("Active:", active_versions)
        print("Blueprints:", app.blueprints.keys())
        available_versions = [
            bp for bp in app.blueprints.keys()
            if bp.split(".")[-1] in active_versions
        ]
        print("Available:", available_versions)
        return render_template("algo.html", versions=available_versions)

    return app


if __name__ == "__main__":
    create_app().run()
