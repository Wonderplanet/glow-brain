  post "event_story_episodes/:id/session", to: "event_story_episodes#create_session"
  delete "event_story_episodes/session", to: "event_story_episodes#delete_session"
  post "event_story_episodes/activate", to: "event_story_episodes#activate"
