  post "group_story_episodes/:id/session", to: "group_story_episodes#create_session"
  delete "group_story_episodes/session", to: "group_story_episodes#delete_session"
  post "group_story_episodes/activate", to: "group_story_episodes#activate"
