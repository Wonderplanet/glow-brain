using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameDefenseTargetQuestComponent : UIObject, IInGameDefenseTargetDelegate
    {
        [SerializeField] UIObject _frameAddEffect;

        void IInGameDefenseTargetDelegate.ShowFrameAddEffect()
        {
            _frameAddEffect.Hidden = false;
        }

        void IInGameDefenseTargetDelegate.HideFrameAddEffect()
        {
            _frameAddEffect.Hidden = true;
        }
    }
}
