from flask import Blueprint, render_template

blueprint = Blueprint("v2", __name__)

@blueprint.get("/")
def index():
  return render_template("v2.html")
