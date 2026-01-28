class MstGroupStoryEpisodeTranslator
  def self.translate(mst_group_story_episode_model)
    view_model = MstGroupStoryEpisodeViewModel.new
    view_model.id = mst_group_story_episode_model.id
    view_model.mst_group_story_chapter_id = mst_group_story_episode_model.mst_group_story_chapter_id
    view_model.episode_number = mst_group_story_episode_model.episode_number
    view_model.name = mst_group_story_episode_model.name
    view_model.point_consume_mst_character_id1 = mst_group_story_episode_model.point_consume_mst_character_id1
    view_model.point_consume_mst_character_id2 = mst_group_story_episode_model.point_consume_mst_character_id2
    view_model.point_consume_mst_character_id3 = mst_group_story_episode_model.point_consume_mst_character_id3
    view_model.point_consume_mst_character_id4 = mst_group_story_episode_model.point_consume_mst_character_id4
    view_model.required_unison_point = mst_group_story_episode_model.required_unison_point
    view_model.dependency_mst_group_story_episode_id = mst_group_story_episode_model.dependency_mst_group_story_episode_id
    view_model
  end
end
