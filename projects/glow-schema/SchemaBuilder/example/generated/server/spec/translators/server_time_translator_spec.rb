require 'rails_helper'

RSpec.describe "ServerTimeTranslator" do
  subject { ServerTimeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, date_time_offset: 0)}

    it do
      view_model = subject
      expect(view_model.is_a?(ServerTimeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.date_time_offset).to eq use_case_data.date_time_offset
    end
  end
end
