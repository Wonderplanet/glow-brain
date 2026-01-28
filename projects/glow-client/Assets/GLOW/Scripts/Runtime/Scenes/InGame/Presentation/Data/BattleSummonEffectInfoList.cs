using System.Collections.Generic;
using UnityEngine;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [CreateAssetMenu(fileName = "BattleSummonEffectInfoList", menuName = "GLOW/ScriptableObject/BattleSummonEffectInfoList")]
    public class BattleSummonEffectInfoList : ScriptableObject
    {
        public List<BattleSummonEffectInfo> List;
    }
}