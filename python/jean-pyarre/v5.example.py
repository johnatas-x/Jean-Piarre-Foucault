from flask import Blueprint, render_template

v5_blueprint = Blueprint('v5', __name__)

@v5_blueprint.route('/algo/v5', methods=['GET'])
def v5_function():
    return render_template('v5.html')
