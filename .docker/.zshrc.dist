####################### ZSH #########################
export ZSH="$HOME/.oh-my-zsh"
export TERM=screen-256color
ZSH_THEME="half-life"
plugins=(git colored-man-pages colorize composer copypath docker docker-compose drush git-auto-fetch command-not-found sudo)
source $ZSH/oh-my-zsh.sh
DISABLE_AUTO_TITLE="true"

PROMPT=$'🐧[%D{%H:%M:%S}] %{$purple%}%n@%m%{$reset_color%} in %{$limegreen%}%~%{$reset_color%}$(ruby_prompt_info " with%{$fg[red]%} " v g "%{$reset_color%}")$vcs_info_msg_0_%{$orange%} λ%{$reset_color%} '

###################### Aliases ######################
alias ll="ls -alh"
alias upcomp="composer update --lock && composer normalize"
