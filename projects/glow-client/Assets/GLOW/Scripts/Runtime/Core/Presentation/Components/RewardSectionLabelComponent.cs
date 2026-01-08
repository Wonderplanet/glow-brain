using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class RewardSectionLabelComponent : UIObject
    {
        [SerializeField] AnimationPlayer _animationPlayer;

        public async UniTask PlayFadeIn(CancellationToken cancellationToken)
        {
            Hidden = false;
            await UniTask.Delay(TimeSpan.FromSeconds(0.3f), cancellationToken: cancellationToken);
        }

        public void ShowRewardLabel()
        {
            Hidden = false;
            _animationPlayer.Skip();
        }
    }
}
