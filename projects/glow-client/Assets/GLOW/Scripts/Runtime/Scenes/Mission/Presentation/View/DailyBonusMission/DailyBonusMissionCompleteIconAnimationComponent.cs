using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.DailyBonusMission
{
    public class DailyBonusMissionCompleteIconAnimationComponent : UIObject
    {
        [SerializeField] AnimationPlayer _animationPlayer;

        public void PlayDefAnimation()
        {
            _animationPlayer.Skip();
        }
        
        public void PlayAnimation()
        {
            _animationPlayer.Play();
        }

        public async UniTask PlayAnimationAsync(CancellationToken cancellationToken)
        {
            await _animationPlayer.PlayAsync(cancellationToken);
        }
    }
}
