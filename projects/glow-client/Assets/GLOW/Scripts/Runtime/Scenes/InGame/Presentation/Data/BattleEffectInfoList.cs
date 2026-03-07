using System.Collections.Generic;
using UnityEngine;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [CreateAssetMenu(fileName = "BattleEffectInfoList", menuName = "GLOW/ScriptableObject/BattleEffectInfoList")]
    public class BattleEffectInfoList : ScriptableObject
    {
        public List<BattleEffectInfo> List;
    }
}