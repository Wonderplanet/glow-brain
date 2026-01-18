using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.BoxGacha.Presentation.Component;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.BoxGacha.Presentation.View
{
    public class BoxGachaTopView : UIView
    {
        [Header("Boxの中身のリスト")]
        [SerializeField] BoxGachaRewardListComponent _rewardListComponent;
        
        [Header("いいジャンくじの背景画像")]
        [SerializeField] UIImage _backgroundImage;
        
        [Header("今のいいジャンくじの状態の表示")]
        [SerializeField] UIText _currentBoxLevelText;
        [SerializeField] UIText _currentReceivedPrizeText;
        
        [Header("いいジャンくじを引くためのアイテムのアイコンと所持数")]
        [SerializeField] UIImage _costItemIconImage;
        [SerializeField] UIText _costItemCountText;
        
        [Header("いいジャンくじをラインナップ表示ボタン")]
        [SerializeField] Button _lineupButton;
        
        [Header("いいジャンくじのBoxをリセットするボタン")]
        [SerializeField] Button _resetBoxButton;
        
        [Header("いいジャンくじのBoxを引くボタン")]
        [SerializeField] Button _drawButton;
        [SerializeField] UIText _drawCostText;
        [SerializeField] UIImage _drawCostImage;
        
        [Header("いいジャンくじのBoxをリセット時のアニメーション")]
        [SerializeField] UIObject _resetBoxAnimationObject;
        [SerializeField] Animator _resetBoxAnimator;
        
        [Header("いいジャンくじを引ける期間の表示")]
        [SerializeField] UIText _remainingTimeSpanText;
        
        [Header("賑やかし用のキャラモーション")]
        [SerializeField] UISpineWithOutlineAvatar _decoUnitFirst;
        [SerializeField] UISpineWithOutlineAvatar _decoUnitSecond;

        // 切り替えInアニメーション名
        const string ResetBoxInAnimationName = "Iijyankuji-Anim-in";
        const string ResetBoxDefAnimationName = "Iijyankuji-Anim-def";
        const string ResetBoxOutAnimationName = "Iijyankuji-Anim-out";

        public void InitializeRewardListView()
        {
            _rewardListComponent.Initialize();
        }
        
        public void SetUpRewardListView(
            IReadOnlyList<BoxGachaRewardListCellViewModel> cellViewModels,
            Action<PlayerResourceIconViewModel> onPrizeIconTapped)
        {
            _rewardListComponent.SetUpRewardList(cellViewModels, onPrizeIconTapped);
        }
        
        public void ResetScrollPosition()
        {
            _rewardListComponent.ResetScrollPosition();
        }

        public void SetUpCurrentBoxInfoText(BoxResetCount boxResetCount)
        {
            _currentBoxLevelText.SetText(ZString.Format("現在：{0}箱目", boxResetCount.ToCurrentBoxNumber()));
        }
        
        public void SetUpCurrentStockText(BoxDrawCount totalDrawCount, BoxGachaPrizeStock totalStock)
        {
            _currentReceivedPrizeText.SetText(
                ZString.Format(
                    "獲得済み報酬：{0}/{1}", 
                    totalDrawCount.Value, 
                    totalStock.Value));
        }

        public void SetUpCostItemInfo(PlayerResourceIconViewModel costItemIconViewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_costItemIconImage.Image, costItemIconViewModel.AssetPath.Value);
            _costItemCountText.SetText(costItemIconViewModel.Amount.ToStringSeparated());
        }
        
        public void SetUpDrawButtonCostInfo(PlayerResourceIconViewModel costItemIconViewModel, CostAmount costAmount)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _drawCostImage.Image, 
                costItemIconViewModel.AssetPath.Value);
            _drawCostText.SetText(ZString.Format("×{0}", costAmount.ToString()));
        }

        public void SetUpBackgroundImage(KomaBackgroundAssetPath komaBackgroundAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _backgroundImage.Image,
                komaBackgroundAssetPath.Value);
        }
        
        public void SetUpRemainingTimeSpan(RemainingTimeSpan remainingTimeSpan)
        {
            _remainingTimeSpanText.SetText(TimeSpanFormatter.FormatUntilEnd(remainingTimeSpan));
        }

        public async UniTask PlayLineupResetInAnimation(CancellationToken cancellationToken)
        {
            _resetBoxAnimationObject.IsVisible = true;
            _resetBoxAnimator.Play(ResetBoxInAnimationName);
            
            await UniTask.WaitUntil(
                () => 
                {
                    var stateInfo = _resetBoxAnimator.GetCurrentAnimatorStateInfo(0);
                    return stateInfo.IsName(ResetBoxDefAnimationName) && stateInfo.normalizedTime >= 1.0f;
                }, 
                cancellationToken: cancellationToken);
        }
        
        public async UniTask PlayLineupResetOutAnimation(CancellationToken cancellationToken)
        {
            _resetBoxAnimator.Play(ResetBoxOutAnimationName);
            
            // "out" ステートが終わるまで待つ
            await UniTask.WaitUntil(
                () => _resetBoxAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1.0f, 
                cancellationToken: cancellationToken);
            
            _resetBoxAnimationObject.IsVisible = false;
        }
        
        public void SetButtonInteractable(bool interactable)
        {
            _drawButton.interactable = interactable;
            _resetBoxButton.interactable = interactable;
            _lineupButton.interactable = interactable;
        }
        
        public void SetUpDecoUnitImage(
            UnitImageAssetPath unitFirstImageAssetPath,
            UnitImageAssetPath unitSecondImageAssetPath,
            UnitImage unitImageFirst,
            UnitImage unitImageSecond)
        {
            if (unitFirstImageAssetPath.IsEmpty())
            {
                _decoUnitFirst.IsVisible = false;
            }
            else
            {
                _decoUnitFirst.IsVisible = true;
                _decoUnitFirst.SetAvatarScale(unitImageFirst.SkeletonScale);
                _decoUnitFirst.SetSkeleton(unitImageFirst.SkeletonAnimation.skeletonDataAsset);
                _decoUnitFirst.Flip = true;
                
                // Joyモーションがあればそちらを再生、なければ通常待機モーションを再生(反転)
                if (_decoUnitFirst.IsFindAnimation(CharacterUnitAnimation.MirrorWaitJoy.Name))
                {
                    _decoUnitFirst.Animate(CharacterUnitAnimation.MirrorWaitJoy.Name);
                }
                else
                {
                    _decoUnitFirst.Animate(CharacterUnitAnimation.MirrorWait.Name);
                }
            }

            if (unitSecondImageAssetPath.IsEmpty())
            {
                _decoUnitSecond.IsVisible = false;
            }
            else
            {
                _decoUnitSecond.IsVisible = true;
                _decoUnitSecond.SetAvatarScale(unitImageSecond.SkeletonScale);
                _decoUnitSecond.SetSkeleton(unitImageSecond.SkeletonAnimation.skeletonDataAsset);
                _decoUnitSecond.Flip = false;
                
                // Joyモーションがあればそちらを再生、なければ通常待機モーションを再生
                if (_decoUnitSecond.IsFindAnimation(CharacterUnitAnimation.WaitJoy.Name))
                {
                    _decoUnitSecond.Animate(CharacterUnitAnimation.WaitJoy.Name);
                }
                else
                {
                    _decoUnitSecond.Animate(CharacterUnitAnimation.Wait.Name);
                }
            }
        }
    }
}