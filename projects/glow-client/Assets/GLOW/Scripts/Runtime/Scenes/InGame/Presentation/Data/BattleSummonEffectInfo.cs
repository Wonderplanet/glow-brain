using System;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.Field;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [Serializable]
    public class BattleSummonEffectInfo
    {
        public BattleEffectId Id;
        public BattleSummonEffectView Prefab;
    }
}