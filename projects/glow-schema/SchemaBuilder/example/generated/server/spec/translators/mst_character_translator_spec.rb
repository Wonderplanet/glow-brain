require 'rails_helper'

RSpec.describe "MstCharacterTranslator" do
  subject { MstCharacterTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, name: 1, asset_key: 2, voice_actor_name: 3, mst_main_artist_group_id: 4, description: 5, serial_number: 6, life_span: 7, birth_day: 8, birth_month: 9, favorite: 10, dislike: 11, height: 12, weight: 13, local_notification_message: 14)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstCharacterViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.asset_key).to eq use_case_data.asset_key
      expect(view_model.voice_actor_name).to eq use_case_data.voice_actor_name
      expect(view_model.mst_main_artist_group_id).to eq use_case_data.mst_main_artist_group_id
      expect(view_model.description).to eq use_case_data.description
      expect(view_model.serial_number).to eq use_case_data.serial_number
      expect(view_model.life_span).to eq use_case_data.life_span
      expect(view_model.birth_day).to eq use_case_data.birth_day
      expect(view_model.birth_month).to eq use_case_data.birth_month
      expect(view_model.favorite).to eq use_case_data.favorite
      expect(view_model.dislike).to eq use_case_data.dislike
      expect(view_model.height).to eq use_case_data.height
      expect(view_model.weight).to eq use_case_data.weight
      expect(view_model.local_notification_message).to eq use_case_data.local_notification_message
    end
  end
end
