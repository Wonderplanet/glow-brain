require 'rails_helper'

RSpec.describe "UserSearchTranslator" do
  subject { UserSearchTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user_id: 0, user_name: 1, rank_level: 2, main_favorite_character_variant: 3, last_accessed_at: 4, relationship: 5, description: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(UserSearchViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user_id).to eq use_case_data.user_id
      expect(view_model.user_name).to eq use_case_data.user_name
      expect(view_model.rank_level).to eq use_case_data.rank_level
      expect(view_model.main_favorite_character_variant).to eq use_case_data.main_favorite_character_variant
      expect(view_model.last_accessed_at).to eq use_case_data.last_accessed_at
      expect(view_model.relationship).to eq use_case_data.relationship
      expect(view_model.description).to eq use_case_data.description
    end
  end
end
