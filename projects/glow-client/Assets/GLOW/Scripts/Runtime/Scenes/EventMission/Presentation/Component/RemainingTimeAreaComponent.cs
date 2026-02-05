using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.EventMission.Presentation.Component
{
    public class RemainingTimeAreaComponent : UIObject
    {
        [SerializeField] UIText[] _remainingTimeTexts;
        
        public UIText[] RemainingTimeTexts => _remainingTimeTexts;
    }
}