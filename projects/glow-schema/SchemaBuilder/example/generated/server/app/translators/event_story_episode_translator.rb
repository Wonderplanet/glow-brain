class EventStoryEpisodeTranslator
  def self.translate(event_story_episode_model)
    view_model = EventStoryEpisodeViewModel.new
    view_model.id = event_story_episode_model.id
    view_model.mst_event_story_episode_id = event_story_episode_model.mst_event_story_episode_id
    view_model.play_count = event_story_episode_model.play_count
    view_model.clear_count = event_story_episode_model.clear_count
    view_model
  end
end
