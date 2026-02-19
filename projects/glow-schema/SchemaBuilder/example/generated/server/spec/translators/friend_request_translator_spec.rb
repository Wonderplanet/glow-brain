require 'rails_helper'

RSpec.describe "FriendRequestTranslator" do
  subject { FriendRequestTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, user_name: 0, user_id: 1, rank_level: 2, description: 3, last_accessed_at: 4, request_type: 5, expire_at: 6, character_variant: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(FriendRequestViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.user_name).to eq use_case_data.user_name
      expect(view_model.user_id).to eq use_case_data.user_id
      expect(view_model.rank_level).to eq use_case_data.rank_level
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.last_accessed_at).to eq use_case_data.last_accessed_at
      expect(view_model.request_type).to eq use_case_data.request_type
      expect(view_model.expire_at).to eq use_case_data.expire_at
      expect(view_model.character_variant).to eq use_case_data.character_variant
    end
  end
end
