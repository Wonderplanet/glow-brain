class UserSearchTranslator
  def self.translate(user_search_model)
    view_model = UserSearchViewModel.new
    view_model.user_id = user_search_model.user_id
    view_model.user_name = user_search_model.user_name
    view_model.rank_level = user_search_model.rank_level
    view_model.main_favorite_character_variant = user_search_model.main_favorite_character_variant
    view_model.last_accessed_at = user_search_model.last_accessed_at
    view_model.relationship = user_search_model.relationship
    view_model.description = user_search_model.description
    view_model
  end
end
