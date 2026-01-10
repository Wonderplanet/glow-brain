#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Definitions;
using GLOW.Debugs.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Data.Repositories
{
    public class InGameDebugSettingRepository : IInGameDebugSettingRepository
    {
        InGameDebugSettingModel _model = InGameDebugSettingModel.Empty with
        {
            OverrideUnitAssetKeys = new List<UnitAssetKey>()
            {
                new UnitAssetKey("enemy_spy_00101"),
                new UnitAssetKey("enemy_spy_00101"),
                new UnitAssetKey("enemy_spy_00101"),
            },
            OverrideSummonParameters = DebugMstUnitTemporaryParameterDefinitions.DebugEnemyStageParameterModels
        };

        public void Save(InGameDebugSettingModel model)
        {
            _model = model;
        }

        public InGameDebugSettingModel Get()
        {
            return _model;
        }
    }
}
#endif
