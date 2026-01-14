class EventStoryReleaseResultTranslator
  def self.translate(event_story_release_result_model)
    view_model = EventStoryReleaseResultViewModel.new
    view_model.event_story_episode = event_story_release_result_model.event_story_episode
    view_model.consumed_crystal = event_story_release_result_model.consumed_crystal
    view_model.consumed_item = event_story_release_result_model.consumed_item
    view_model
  end
end
