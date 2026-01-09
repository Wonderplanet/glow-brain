using GLOW.Scenes.InGame.Data.Data;

namespace GLOW.Scenes.InGame.Data.DataStores
{
    public class InGameSettingDataStore : IInGameSettingDataStore
    {
        readonly InGameSettingData _inGameSettingData;

        public InGameSettingDataStore(InGameSettingData inGameSettingData)
        {
            _inGameSettingData = inGameSettingData;
        }

        public InGameSettingData GetInGameSetting()
        {
            return _inGameSettingData;
        }
    }
}
