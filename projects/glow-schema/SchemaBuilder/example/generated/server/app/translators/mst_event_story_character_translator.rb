class MstEventStoryCharacterTranslator
  def self.translate(mst_event_story_character_model)
    view_model = MstEventStoryCharacterViewModel.new
    view_model.id = mst_event_story_character_model.id
    view_model.mst_event_story_episode_id = mst_event_story_character_model.mst_event_story_episode_id
    view_model.mst_character_id = mst_event_story_character_model.mst_character_id
    view_model
  end
end
