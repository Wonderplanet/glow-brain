class SoloStoryEpisodeTranslator
  def self.translate(solo_story_episode_model)
    view_model = SoloStoryEpisodeViewModel.new
    view_model.id = solo_story_episode_model.id
    view_model.mst_solo_story_episode_id = solo_story_episode_model.mst_solo_story_episode_id
    view_model.play_count = solo_story_episode_model.play_count
    view_model.clear_count = solo_story_episode_model.clear_count
    view_model
  end
end
