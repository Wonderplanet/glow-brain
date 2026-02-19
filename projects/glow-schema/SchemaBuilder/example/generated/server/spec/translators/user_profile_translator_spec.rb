require 'rails_helper'

RSpec.describe "UserProfileTranslator" do
  subject { UserProfileTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user_name: 0, favorite_mst_character_variant: 1, support_character_variant: 2, relationship: 3, rank_level: 4, last_accessed_at: 5, description: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(UserProfileViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user_name).to eq use_case_data.user_name
      expect(view_model.favorite_mst_character_variant).to eq use_case_data.favorite_mst_character_variant
      expect(view_model.support_character_variant).to eq use_case_data.support_character_variant
      expect(view_model.relationship).to eq use_case_data.relationship
      expect(view_model.rank_level).to eq use_case_data.rank_level
      expect(view_model.last_accessed_at).to eq use_case_data.last_accessed_at
      expect(view_model.description).to eq use_case_data.description
    end
  end
end
