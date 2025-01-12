from flask import Blueprint, render_template

blueprint = Blueprint("v5", __name__)

@blueprint.route('/')
def index():
    return render_template('v5.html')
