require 'rails_helper'

RSpec.describe "OprHomeBannerTranslator" do
  subject { OprHomeBannerTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, destination: 1, destination_id: 2, banner_path: 3, start_at: 4, end_at: 5, sort_number: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprHomeBannerViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.destination).to eq use_case_data.destination
      expect(view_model.destination_id).to eq use_case_data.destination_id
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.sort_number).to eq use_case_data.sort_number
    end
  end
end
