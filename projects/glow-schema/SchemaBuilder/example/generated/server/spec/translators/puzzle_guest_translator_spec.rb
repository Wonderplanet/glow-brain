require 'rails_helper'

RSpec.describe "PuzzleGuestTranslator" do
  subject { PuzzleGuestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user_id: 0, user_name: 1, rank_level: 2, relationship: 3, support_character_variant: 4)}

    it do
      view_model = subject
      expect(view_model.is_a?(PuzzleGuestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user_id).to eq use_case_data.user_id
      expect(view_model.user_name).to eq use_case_data.user_name
      expect(view_model.rank_level).to eq use_case_data.rank_level
      expect(view_model.relationship).to eq use_case_data.relationship
      expect(view_model.support_character_variant).to eq use_case_data.support_character_variant
    end
  end
end
