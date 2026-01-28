using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaResult.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-4_ガシャ結果
    /// </summary>
    public enum GachaResultState
    {
        PlayingPopupAnimation,
        PlayingConvertAnimation,
        EndConvertAnimation,
        PlayingAvatarPopupAnimation,
        EndAnimation,
    }

    public class GachaResultViewController : UIViewController<GachaResultView>, IEscapeResponder
    {
        [Inject] IGachaResultViewDelegate ViewDelegate { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyHandler { get; }

         List<PlayerResourceIconViewModel> _convertedViewModel = new List<PlayerResourceIconViewModel>();
         List<PlayerResourceIconViewModel> _avatarViewModel = new List<PlayerResourceIconViewModel>();
         GachaType _gachaType;
         GachaResultState _gachaResultState;
         MasterDataId _gachaId;
         GachaDrawType _gachaDrawType;
         PreConversionResourceExistenceFlag _existsPreConversionResource;

         bool _isInAppReviewDisplay;

         public MasterDataId GachaId
         {
             get => _gachaId;
             set => _gachaId = value;
         }

         public GachaDrawType GachaDrawType
         {
             get => _gachaDrawType;
             set => _gachaDrawType = value;
         }


         public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public void SetIsGachaReDrawable(DrawableFlag reDrawableFlag)
        {
            ActualView.SetGachaDrawButton(reDrawableFlag);
        }

        public void SetupGachaResultView(GachaResultViewModel viewModel)
        {
            _gachaResultState = GachaResultState.PlayingPopupAnimation;
            _convertedViewModel = viewModel.ConvertedCellViewModels;
            _existsPreConversionResource = viewModel.ExistsPreConversionResource;
            _gachaType = viewModel.GachaType;

            // ガシャのタイプで背景を設定する
            ActualView.SetColorByGachaType(viewModel.GachaType);

            // 獲得アバターがあるか判定
            bool isGetAvatar = viewModel.AvatarViewModels.Count > 0;
            // 変換済みかけらがあるか判定
            var isGetFragment = viewModel.ExistsPreConversionResource;

            ActualView.SetIconModels(
                viewModel.CellViewModels,
                viewModel.ConvertedCellViewModels,
                OnCompleteCellAnimationAction,
                isGetFragment,
                isGetAvatar,
                OnIconCellTapped,
                ViewDelegate.ShowInAppReview);

            if (viewModel.AvatarViewModels.Count > 0)
            {
                _avatarViewModel = viewModel.AvatarViewModels;
            }
        }

        public void StartAnimation()
        {
            ActualView.StartAnimation();
        }

        void OnCompleteCellAnimationAction()
        {
            ProgressState();
        }

        void ProgressState()
        {
            if(_gachaResultState is GachaResultState.EndAnimation)
            {
                return;
            }

            // 獲得アバターがない場合、変換後終了 通らない想定
            if (_gachaResultState is GachaResultState.EndConvertAnimation &&
                _avatarViewModel.Count == 0)
            {
                return;
            }

            _gachaResultState++;
        }

        void OnIconCellTapped(PlayerResourceIconViewModel model)
        {
            ViewDelegate.OnIconCellTapped(model);
        }

        [UIAction]
        public void OnBackGroundTapped()
        {
            // かけら変換アニメーション中と終了後はタップ無効
            if (_gachaResultState is GachaResultState.PlayingConvertAnimation or GachaResultState.EndAnimation)
            {
                return;
            }

            // セルポップアップアニメーション中のタップでスキップ
            if (_gachaResultState is GachaResultState.PlayingPopupAnimation)
            {
                ProgressState();

                var isGetAvatar = !_avatarViewModel.IsEmpty();
                ActualView.SkipCellAnimation(_convertedViewModel, isGetAvatar, _existsPreConversionResource);
                return;
            }

            if (_gachaResultState is GachaResultState.PlayingAvatarPopupAnimation)
            {
                ProgressState();
                ActualView.SkipAvatarCellAnimation();
                return;
            }

            // かけら変換後のタップでアバター獲得表示画面に遷移
            if(_gachaResultState is GachaResultState.EndConvertAnimation &&
               _avatarViewModel.Count > 0)
            {
                ProgressState();
                ActualView.SetAvatarModel(_avatarViewModel);
                // アバター表示時に選択音を鳴らす
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                return;
            }
        }

        public void CloseGachaResult()
        {
            ViewDelegate.ExitGachaResult();
            HomeViewNavigation.TryPop();
        }

        [UIAction]
        public void OnReDrawButtonTapped()
        {
            // もう一回ボタンタップ時
            ViewDelegate.OnReDrawButtonTapped(GachaId, GachaDrawType);
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            CloseGachaResult();
        }

        [UIAction]
        public void OnTutorialConfirmButtonTapped()
        {
            if(_gachaType is not GachaType.Tutorial) return;

            ViewDelegate.OnTutorialConfirmButtonTapped();
        }

        [UIAction]
        public void OnTutorialReDrawButtonTapped()
        {
            if(_gachaType is not GachaType.Tutorial) return;

            ViewDelegate.OnTutorialReDrawButtonTapped(GachaId, GachaDrawType);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;

            // チュートリアル中はバックキーを無効化
            if (TutorialBackKeyHandler.IsPlayingTutorial())
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }

            if(_gachaResultState is GachaResultState.EndConvertAnimation || _gachaResultState is GachaResultState.EndAnimation)
            {
                CloseGachaResult();
                return true;
            }

            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }
    }
}
