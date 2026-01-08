using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleHighScoreRewardListCell : UICollectionViewCell
    {
        [SerializeField] Button _rewardButton;
        [SerializeField] UIImage _rewardThresholdImage;
        [SerializeField] PlayerResourceIconComponent _playerResourceIconComponent;
        [SerializeField] AdventBattleHighScorePlateComponent _highScorePlateComponent;
        [SerializeField] AdventBattleHighScoreRewardAnimationComponent _animationComponent;
        [SerializeField] UIImage _rewardIconImage;
        
        Button.ButtonClickedEvent OnRewardIconTapped => _rewardButton.onClick;
        
        static readonly Color _rewardIconGrayOutCoefficient = new Color(0.51f, 0.51f, 0.51f, 1f);
        static readonly Color _rewardIconDefaultCoefficient = new Color(1f, 1f, 1f,1f);

        public override void DidEndDisplaying()
        {
            base.DidEndDisplaying();
            
            // アイコン押下時の処理を設定
            OnRewardIconTapped.RemoveAllListeners();
            _animationComponent.DidEndDisplaying();
        }

        public void SetRewardIcon(
            PlayerResourceIconViewModel viewModel, 
            AdventBattleHighScoreRewardObtainedFlag flag)
        {
            _playerResourceIconComponent.Hidden = false;
            
            // アイコンの読み込みについて、初期位置変更後に再度ロードがかかる可能性があるためクリアしておく
            SpriteLoaderUtil.Clear(_rewardIconImage.Image);
            _playerResourceIconComponent.Setup(viewModel);
            _playerResourceIconComponent.SetAmount(PlayerResourceAmount.Empty);
            
            if (flag)
            {
                _rewardIconImage.Image.color = _rewardIconGrayOutCoefficient;
            }
            else
            {
                _rewardIconImage.Image.color = _rewardIconDefaultCoefficient;
            }
        }
        
        public void SetHighScoreComponent(AdventBattleScore highScore, bool isPickup)
        {
            _highScorePlateComponent.SetupHighScorePlate(highScore, isPickup);
            
        }

        public void SetRewardIconTappedAction(
            PlayerResourceIconViewModel viewModel, 
            Action<PlayerResourceIconViewModel> rewardAction)
        {
            // 宝箱押下時の処理を設定(宝箱をタップした時に報酬一覧を表示する吹き出しを出す)
            OnRewardIconTapped.RemoveAllListeners();
            OnRewardIconTapped.AddListener(() =>
            {
                rewardAction?.Invoke(viewModel);
            });
        }
        
        public void SetupObtainedRewardIcon(AdventBattleHighScoreRewardObtainedFlag flag)
        {
            if (flag)
            {
                _animationComponent.SkipCheckAnimation();
            }
        }
        
        public void PlayPickUpRewardAnimation(AdventBattleHighScoreRewardObtainedFlag flag, bool isPickUp)
        {
            if (isPickUp && !flag)
            {
                _animationComponent.PlayPickUpRewardAnimation();
            }
        }
        
        public async UniTask PlayObtainRewardAnimation(CancellationToken cancellationToken)
        {
            PlayRewardIconColorAnimation();
            await _animationComponent.PlayGetRewardAnimation(cancellationToken);
        }
        
        void PlayRewardIconColorAnimation()
        {
            _rewardIconImage.Image.DOColor(_rewardIconGrayOutCoefficient, 0.2f);
        }
    }
}