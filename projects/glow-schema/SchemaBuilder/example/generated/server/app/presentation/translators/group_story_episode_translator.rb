class GroupStoryEpisodeTranslator
  def self.translate(group_story_episode_model)
    view_model = GroupStoryEpisodeViewModel.new
    view_model.id = group_story_episode_model.id
    view_model.mst_group_story_episode_id = group_story_episode_model.mst_group_story_episode_id
    view_model.play_count = group_story_episode_model.play_count
    view_model.clear_count = group_story_episode_model.clear_count
    view_model
  end
end
