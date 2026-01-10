require 'rails_helper'

RSpec.describe "FriendRequestIndexTranslator" do
  subject { FriendRequestIndexTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, total_count: 0, limit: 1, offset: 2, friend_requests: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(FriendRequestIndexViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.total_count).to eq use_case_data.total_count
      expect(view_model.limit).to eq use_case_data.limit
      expect(view_model.offset).to eq use_case_data.offset
      expect(view_model.friend_requests).to eq use_case_data.friend_requests
    end
  end
end
