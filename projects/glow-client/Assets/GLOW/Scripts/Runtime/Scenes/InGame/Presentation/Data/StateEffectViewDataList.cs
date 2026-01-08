using System.Collections.Generic;
using UnityEngine;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Presentation.Data
{
    [CreateAssetMenu(fileName = "StateEffectViewDataList", menuName = "GLOW/ScriptableObject/StateEffectViewDataList")]
    public class StateEffectViewDataList : ScriptableObject
    {
        public List<StateEffectViewData> List;
    }
}
