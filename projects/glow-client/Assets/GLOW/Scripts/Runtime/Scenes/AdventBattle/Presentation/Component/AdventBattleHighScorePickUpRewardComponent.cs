using System;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleHighScorePickUpRewardComponent : UIObject
    {
        [SerializeField] Button _rewardButton;
        [SerializeField] UIImage _rewardIconImage;
        [SerializeField] UIImage _rewardIconGrayOutImage;
        [SerializeField] UIImage _obtainedRewardIconImage;
        [SerializeField] UIText _grayOutText;
        [SerializeField] Animator _animator;

        const string ActiveEffectAnimationName = "EF-Flash";
        const string DeactiveEffectAnimationName = "EF-Flash-Deactive";
        
        Button.ButtonClickedEvent OnRewardIconTapped => _rewardButton.onClick;
        
        string _playAnimationName;
        
        public void Setup(
            AdventBattleHighScoreRewardViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardAction)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _rewardIconImage.Image,
                viewModel.RewardViewModel.AssetPath.Value);
            
            _grayOutText.Hidden = !viewModel.ObtainedFlag;
            _rewardIconGrayOutImage.Hidden = !viewModel.ObtainedFlag;
            _obtainedRewardIconImage.Hidden = !viewModel.ObtainedFlag;
            
            _playAnimationName = viewModel.ObtainedFlag ? DeactiveEffectAnimationName : ActiveEffectAnimationName;
            
            PlayPickUpRewardEffect();
                
            // 宝箱押下時の処理を設定(宝箱をタップした時に報酬一覧を表示する吹き出しを出す)
            OnRewardIconTapped.RemoveAllListeners();
            OnRewardIconTapped.AddListener(() =>
            {
                rewardAction?.Invoke(viewModel.RewardViewModel);
            });
        }

        public void PlayPickUpRewardEffect()
        {
            _animator.Play(_playAnimationName);
        }
    }
}