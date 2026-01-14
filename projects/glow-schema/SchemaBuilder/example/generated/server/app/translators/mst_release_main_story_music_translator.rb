class MstReleaseMainStoryMusicTranslator
  def self.translate(mst_release_main_story_music_model)
    view_model = MstReleaseMainStoryMusicViewModel.new
    view_model.mst_main_episode_id = mst_release_main_story_music_model.mst_main_episode_id
    view_model.mst_music_id = mst_release_main_story_music_model.mst_music_id
    view_model
  end
end
