using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleHighScoreRewardAnimationComponent : UIBehaviour
    {
        [SerializeField] UIObject _rewardEffectObject;
        [SerializeField] UIObject _decoEffectObject;
        [SerializeField] UIObject _checkEffectObject;
        
        [SerializeField] Animator _rewardAnimator;
        [SerializeField] UIAnimation _checkAnimation;
        
        [SerializeField] string _rewardAnimationName;
        
        public void DidEndDisplaying()
        {
            _rewardEffectObject.Hidden = true;
            _decoEffectObject.Hidden = true;
            _checkEffectObject.Hidden = true;
        }

        public void SkipCheckAnimation()
        {
            _checkEffectObject.Hidden = false;
            _rewardEffectObject.Hidden = true;
            _decoEffectObject.Hidden = true;
        }

        public void PlayPickUpRewardAnimation()
        {
            _rewardEffectObject.Hidden = false;
            _decoEffectObject.Hidden = false;
            _checkEffectObject.Hidden = true;
        }
        
        public async UniTask PlayGetRewardAnimation(CancellationToken cancellationToken)
        {
            _decoEffectObject.Hidden = true;
            
            _checkEffectObject.Hidden = false;
            _checkAnimation.Play();
            await UniTask.Delay(15, cancellationToken: cancellationToken);
            
            _rewardEffectObject.Hidden = false;
            _rewardAnimator.Play(_rewardAnimationName, 0, 0);
        }
    }
}