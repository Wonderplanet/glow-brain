using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionBonusPointRewardBoxAnimationController : UIBehaviour
    {
        [SerializeField] Animator _bonusRewardAnimator;
        [SerializeField] string _normalAnimationName;
        [SerializeField] string _openAnimationName;
        [SerializeField] string _openedAnimationName;
        [SerializeField] string _acceptAnimationName;
        
        public void PlayNormalAnimation()
        {
            _bonusRewardAnimator.Play(_normalAnimationName);
        }
        
        public void PlayAcceptAnimation()
        {
            _bonusRewardAnimator.Play(_acceptAnimationName);
        }
        
        public void PlayOpenedAnimation()
        {
            _bonusRewardAnimator.Play(_openedAnimationName);
        }
        
        public void PlayOpenAnimation()
        {
            _bonusRewardAnimator.Play(_openAnimationName);
        }
        
        public async UniTask PlayOpenAnimationAsync(CancellationToken cancellationToken)
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_061_001);
            _bonusRewardAnimator.Play(_openAnimationName);

            await WaitBoxOpenAnimationComplete(cancellationToken);
        }

        async UniTask WaitBoxOpenAnimationComplete(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(() =>  _bonusRewardAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1, cancellationToken: cancellationToken);
        }
    }
}