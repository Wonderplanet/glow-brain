class MstMainStoryEpisodeCharacterTranslator
  def self.translate(mst_main_story_episode_character_model)
    view_model = MstMainStoryEpisodeCharacterViewModel.new
    view_model.id = mst_main_story_episode_character_model.id
    view_model.mst_main_story_episode_id = mst_main_story_episode_character_model.mst_main_story_episode_id
    view_model.mst_character_id = mst_main_story_episode_character_model.mst_character_id
    view_model
  end
end
