class MstMainStoryEpisodeTranslator
  def self.translate(mst_main_story_episode_model)
    view_model = MstMainStoryEpisodeViewModel.new
    view_model.id = mst_main_story_episode_model.id
    view_model.episode_number = mst_main_story_episode_model.episode_number
    view_model.mst_main_story_chapter_id = mst_main_story_episode_model.mst_main_story_chapter_id
    view_model.name = mst_main_story_episode_model.name
    view_model.necessary_user_rank = mst_main_story_episode_model.necessary_user_rank
    view_model.dependency_mst_main_quest_id = mst_main_story_episode_model.dependency_mst_main_quest_id
    view_model.is_movie = mst_main_story_episode_model.is_movie
    view_model.release_at = mst_main_story_episode_model.release_at
    view_model
  end
end
