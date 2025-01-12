from flask import Flask, render_template
from dotenv import load_dotenv
from pathlib import Path
import os
import mysql.connector
import importlib.util

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

    def get_versions(self) -> list[str]:
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

    def register_blueprints(self, app: Flask, versions: list[str]):
        for version in versions:
            module = self.load_module(version)
            if module and hasattr(module, "blueprint"):
                module.blueprint.url_prefix = f"/algo/{version}"
                app.register_blueprint(module.blueprint)


# Init Flask app and managers.
app = Flask(__name__)
db_manager = DBManager()
version_manager = VersionManager(versions_dir=Path("versions"))

# Intersect DB versions & file versions.
db_versions = db_manager.get_versions()
file_versions = [file.stem for file in Path("versions").glob("*.py")]
available_versions = list(set(db_versions) & set(file_versions))

# Register available blueprints.
version_manager.register_blueprints(app, available_versions)


@app.route("/algo")
def algo():
    return render_template("algo.html", versions=available_versions)

if __name__ == "__main__":
    app.run()
