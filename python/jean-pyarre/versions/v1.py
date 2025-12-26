from flask import Blueprint, render_template

blueprint = Blueprint("v1", __name__)

@blueprint.get("/")
def index():
  return render_template("v1.html")
