require 'rails_helper'

RSpec.describe "FriendRequestCreateResultTranslator" do
  subject { FriendRequestCreateResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, friend_request: 0, updated_mission: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(FriendRequestCreateResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.friend_request).to eq use_case_data.friend_request
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
    end
  end
end
