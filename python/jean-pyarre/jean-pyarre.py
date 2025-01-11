from dotenv import load_dotenv
import os
from flask import Flask, render_template
import mysql.connector

load_dotenv()

class DBManager:
    def __init__(self, database=os.getenv("DB_NAME"), host=os.getenv("DB_HOST"), user=os.getenv("DB_USER"), password=os.getenv("DB_PASSWORD")):
        try:
            self.connection = mysql.connector.connect(
                user=user,
                password=password,
                host=host,
                database=database,
                auth_plugin='mysql_native_password'
            )
            self.cursor = self.connection.cursor()
        except mysql.connector.Error as err:
            self.connection = None
            self.cursor = None

    def get_versions(self):
        if self.cursor is None:
            return []
        try:
            self.cursor.execute('SELECT version FROM lotto_versions;')
            versions = [version[0] for version in self.cursor]
            return versions
        except mysql.connector.Error as err:
            return []
        finally:
            if self.cursor:
                self.cursor.close()
            if self.connection:
                self.connection.close()

app  = Flask(__name__)
conn = None

for version in DBManager().get_versions():
    route_file = f'{version}.py'
    module_name = f'{version}'

    if os.path.exists(route_file):
        module = __import__(module_name)
        blueprint = getattr(module, f'{module_name}_blueprint')
        app.register_blueprint(blueprint)

@app.route('/algo')
def algo():
    global conn
    if not conn:
        conn = DBManager()

    return render_template('algo.html', versions=conn.get_versions())

if __name__ == '__main__':
    app.run()
