using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View
{
    public class PvpBattleFinishAnimationView : UIView
    {
        [SerializeField] CanvasGroup _rootCanvasGroup;
        [SerializeField] Animator _animator;
        [SerializeField] UIImage _opponentBarImage;
        [SerializeField] UIImage _playerWinBarImage;
        [SerializeField] UIImage _playerLoseBarImage;
        [SerializeField] Button _closeButton;

        public void InitializeProgressBar(PvpResultEvaluator.PvpResultType pvpResultType)
        {
            _opponentBarImage.Image.fillAmount = 0.0f;
            _playerWinBarImage.Image.fillAmount = 0.0f;
            _playerLoseBarImage.Image.fillAmount = 0.0f;
            
            _opponentBarImage.IsVisible = true;
            _playerWinBarImage.IsVisible = pvpResultType == PvpResultEvaluator.PvpResultType.Victory;
            _playerLoseBarImage.IsVisible = pvpResultType == PvpResultEvaluator.PvpResultType.Defeat;
        }
        
        public async UniTask PlayOutPostHpZeroFinishAnimation(
            CancellationToken cancellationToken,
            PvpResultEvaluator.PvpResultType pvpResultType,
            PvpMaxDistanceRatio playerMaxDistanceRatio,
            PvpMaxDistanceRatio opponentMaxDistanceRatio)
        {
            _animator.SetBool("Win", pvpResultType == PvpResultEvaluator.PvpResultType.Victory);
            _animator.SetBool("Lose", pvpResultType == PvpResultEvaluator.PvpResultType.Defeat);
            _animator.SetTrigger("in");
            
            var progressBarTask = PlayProgressBarAnimation(
                cancellationToken,
                pvpResultType,
                playerMaxDistanceRatio,
                opponentMaxDistanceRatio,
                PvpResultEvaluator.PvpFinishType.OutPostHpZero);
            
            var animationTask = UniTask.WaitUntil(
                () => _animator.GetCurrentAnimatorStateInfo(0).normalizedTime > 1.0f,
                cancellationToken: cancellationToken);
            await UniTask.WhenAll(progressBarTask, animationTask);
        }
        
        public async UniTask PlayTimeUpFinishAnimation(
            CancellationToken cancellationToken,
            PvpResultEvaluator.PvpResultType pvpResultType,
            PvpMaxDistanceRatio playerMaxDistanceRatio,
            PvpMaxDistanceRatio opponentMaxDistanceRatio)
        {
            _animator.SetTrigger("in");
            
            await UniTask.WaitUntil(
                () => _animator.GetCurrentAnimatorStateInfo(0).normalizedTime > 1.0f,
                cancellationToken: cancellationToken);
            
            var progressBarTask = PlayProgressBarAnimation(
                cancellationToken,
                pvpResultType,
                playerMaxDistanceRatio,
                opponentMaxDistanceRatio,
                PvpResultEvaluator.PvpFinishType.MaxDistance);
            
            await UniTask.WaitUntil(
                () => _animator.GetCurrentAnimatorStateInfo(0).normalizedTime > 1.5f ,
                cancellationToken: cancellationToken);
            
            _animator.SetBool("Win", pvpResultType == PvpResultEvaluator.PvpResultType.Victory);
            _animator.SetBool("Lose", pvpResultType == PvpResultEvaluator.PvpResultType.Defeat);
            _animator.SetBool("Bar", true);
            
            var animationTask = UniTask.WaitUntil(
                () => _animator.GetCurrentAnimatorStateInfo(0).normalizedTime > 1.0f,
                cancellationToken: cancellationToken);
            
            await UniTask.WhenAll(progressBarTask, animationTask);
        }
        
        public void SetCloseButtonInteractable(bool interactable)
        {
            _closeButton.interactable = interactable;
        }

        async UniTask PlayProgressBarAnimation(
            CancellationToken cancellationToken,
            PvpResultEvaluator.PvpResultType pvpResultType,
            PvpMaxDistanceRatio playerMaxDistanceRatio,
            PvpMaxDistanceRatio opponentMaxDistanceRatio,
            PvpResultEvaluator.PvpFinishType pvpFinishType)
        {
            var duration = pvpFinishType == PvpResultEvaluator.PvpFinishType.OutPostHpZero ? 0.2f : 1.0f;
            var delayDuration = pvpFinishType == PvpResultEvaluator.PvpFinishType.OutPostHpZero ? 1.2f : 0.0f;
            
            var oppositeBarFillAmount =  _opponentBarImage.Image
                .DOFillAmount(opponentMaxDistanceRatio.Value, duration)
                .SetDelay(delayDuration)
                .SetEase(Ease.Linear)
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);

            var playerBar = pvpResultType == PvpResultEvaluator.PvpResultType.Victory
                ? _playerWinBarImage.Image
                : _playerLoseBarImage.Image;
            
            var playerBarFillAmount = playerBar
                .DOFillAmount(playerMaxDistanceRatio.Value, duration)
                .SetDelay(delayDuration)
                .SetEase(Ease.Linear)
                .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken:cancellationToken);
            
            await UniTask.WhenAll(oppositeBarFillAmount, playerBarFillAmount);
        }
    }
}