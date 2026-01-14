using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.BoxGachaResult.Presentation.ViewModel;
using GLOW.Scenes.GachaResult.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BoxGachaResult.Presentation.View
{
    public class BoxGachaResultViewController : 
        UIViewController<BoxGachaResultView>,
        IEscapeResponder
    {
        public record Argument(BoxGachaResultViewModel ViewModel);
        [Inject] IBoxGachaResultViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        IReadOnlyList<PlayerResourceIconViewModel> _convertedViewModel = new List<PlayerResourceIconViewModel>();
        IReadOnlyList<PlayerResourceIconViewModel> _avatarViewModel = new List<PlayerResourceIconViewModel>();
        GachaResultState _gachaResultState;
        PreConversionResourceExistenceFlag _existsPreConversionResource;
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public void SetUpResult(BoxGachaResultViewModel viewModel)
        {
            _gachaResultState = GachaResultState.PlayingPopupAnimation;
            _convertedViewModel = viewModel.ConvertedCellViewModels;
            _existsPreConversionResource = viewModel.ExistsPreConversionResource;
            
            bool isGetAvatar = viewModel.AvatarViewModels.Count > 0;
            // 変換済みかけらがあるか判定
            var isGetFragment = viewModel.ExistsPreConversionResource;
            
            ActualView.SetIconModels(
                viewModel.CellViewModels,
                viewModel.ConvertedCellViewModels,
                isGetFragment,
                isGetAvatar,
                OnIconCellTapped,
                OnCompleteCellAnimationAction);

            if (viewModel.AvatarViewModels.Count > 0)
            {
                _avatarViewModel = viewModel.AvatarViewModels;
            }
        }
        
        public void StartAnimation()
        {
            ActualView.StartAnimation();
        }
        
        public async UniTask PlayOpenAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayOpenAnimation(cancellationToken);
        }
        
        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayCloseAnimation(cancellationToken);
        }
        
        void OnCompleteCellAnimationAction()
        {
            ProgressState();
            
            if(_gachaResultState == GachaResultState.EndAnimation)
            {
                ActualView.FadeInCloseText();
                ActualView.SetNextButtonVisible(false);
                return;
            }

            // 獲得アバターがない場合、変換後終了 通らない想定
            if (_gachaResultState == GachaResultState.EndConvertAnimation &&
                _avatarViewModel.Count == 0)
            {
                ActualView.FadeInCloseText();
                ActualView.SetNextButtonVisible(false);
            }
        }
        
        void ProgressState()
        {
            if(_gachaResultState == GachaResultState.EndAnimation)
            {
                return;
            }

            // 獲得アバターがない場合、変換後終了 通らない想定
            if (_gachaResultState == GachaResultState.EndConvertAnimation &&
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
        
        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;

            if(_gachaResultState == GachaResultState.EndConvertAnimation || 
               _gachaResultState == GachaResultState.EndAnimation)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
                ViewDelegate.OnCloseButtonTapped();
                return true;
            }
            
            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            switch (_gachaResultState)
            {
                case GachaResultState.EndAnimation:
                case GachaResultState.EndConvertAnimation when 
                    _avatarViewModel.Count == 0:
                    ViewDelegate.OnCloseButtonTapped();
                    break;
                default:
                    break;
            }
        }
        
        [UIAction]
        public void OnBackGroundTapped()
        {
            // かけら変換アニメーション中と終了後はタップ無効
            if (_gachaResultState == GachaResultState.PlayingConvertAnimation || 
                _gachaResultState == GachaResultState.EndAnimation)
            {
                return;
            }

            // セルポップアップアニメーション中のタップでスキップ
            if (_gachaResultState == GachaResultState.PlayingPopupAnimation)
            {
                ProgressState();

                var isGetAvatar = !_avatarViewModel.IsEmpty();
                ActualView.SkipCellAnimation(_convertedViewModel, isGetAvatar, _existsPreConversionResource);
                return;
            }

            if (_gachaResultState == GachaResultState.PlayingAvatarPopupAnimation)
            {
                ProgressState();
                ActualView.SkipAvatarCellAnimation();
                return;
            }

            // かけら変換後のタップでアバター獲得表示画面に遷移
            if(_gachaResultState == GachaResultState.EndConvertAnimation &&
               _avatarViewModel.Count > 0)
            {
                ProgressState();
                ActualView.SetNextTextVisible(false);
                ActualView.SetAvatarModel(_avatarViewModel);
                // アバター表示時に選択音を鳴らす
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                return;
            }
        }
    }
}