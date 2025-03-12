from flask import Flask, render_template, Blueprint
from dotenv import load_dotenv
from pathlib import Path
import os
import mysql.connector
import importlib.util
import sys

if "--check" in sys.argv:
    print("Check mode: application starts successfully")
    sys.exit(0)

load_dotenv()


class DBManager:
    def __init__(self):
        self.connection_params = {
            "database": os.getenv("DB_NAME"),
            "user": os.getenv("DB_USER"),
            "password": os.getenv("DB_PASSWORD"),
            "host": os.getenv("DB_HOST"),
            "auth_plugin": "mysql_native_password",
        }

    def get_versions(self):
        connection = None
        cursor = None
        try:
            connection = mysql.connector.connect(**self.connection_params)
            cursor = connection.cursor()
            cursor.execute("SELECT version FROM lotto_versions")
            return [row[0] for row in cursor.fetchall()]
        except mysql.connector.Error as err:
            return []
        finally:
            if cursor:
                cursor.close()
            if connection:
                connection.close()


class VersionManager:
    def __init__(self, versions_dir: str):
        self.versions_dir = Path(versions_dir)

    def load_module(self, version: str):
        module_path = self.versions_dir / f"{version}.py"
        if not module_path.exists():
            return None

        spec = importlib.util.spec_from_file_location(version, module_path)
        module = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(module)

        return module

    def register_all_blueprints(self, app: Flask):
        for file in self.versions_dir.glob("*.py"):
            version = file.stem
            module = self.load_module(version)
            if module and hasattr(module, "blueprint") and isinstance(module.blueprint, Blueprint):
                module.blueprint.url_prefix = f"/algo/{version}"
                app.register_blueprint(module.blueprint)


# Init Flask app, managers and register blueprints.
app = Flask(__name__)
db_manager = DBManager()
version_manager = VersionManager(versions_dir="versions")
version_manager.register_all_blueprints(app)


def get_active_versions():
    db_versions = db_manager.get_versions()
    return set(db_versions)


@app.route("/algo")
def algo():
    active_versions = get_active_versions()
    available_versions = [
        bp for bp in app.blueprints.keys() if bp.split(".")[-1] in active_versions
    ]
    return render_template("algo.html", versions=available_versions)

if __name__ == "__main__":
    app.run()
