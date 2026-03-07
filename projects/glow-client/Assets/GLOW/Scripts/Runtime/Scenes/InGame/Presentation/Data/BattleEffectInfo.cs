using System;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.Field;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [Serializable]
    public class BattleEffectInfo
    {
        public BattleEffectId Id;
        public BaseBattleEffectView Prefab;
    }
}