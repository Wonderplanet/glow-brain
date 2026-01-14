class MstSoloStoryEpisodeTranslator
  def self.translate(mst_solo_story_episode_model)
    view_model = MstSoloStoryEpisodeViewModel.new
    view_model.id = mst_solo_story_episode_model.id
    view_model.mst_solo_story_chapter_id = mst_solo_story_episode_model.mst_solo_story_chapter_id
    view_model.episode_number = mst_solo_story_episode_model.episode_number
    view_model.name = mst_solo_story_episode_model.name
    view_model
  end
end
