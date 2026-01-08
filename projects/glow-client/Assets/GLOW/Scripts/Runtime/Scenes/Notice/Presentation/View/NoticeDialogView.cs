using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Notice.Presentation.Component;
using GLOW.Scenes.Notice.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Notice.Presentation.View
{
    /// <summary>
    /// 12-3_ノーティス
    /// </summary>
    public class NoticeDialogView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UITextButton _transitionButton;
        [SerializeField] UIText _transitButtonText;
        [SerializeField] NoticeBannerComponent _rawBannerComponent;
        [SerializeField] NoticeMessageComponent _messageComponent;

        public void Setup(NoticeViewModel viewModel)
        {
            _titleText.SetText(viewModel.Title.Value);
            _messageComponent.SetupMessageText(viewModel.Message);

            // 遷移ボタン
            _transitionButton.gameObject.SetActive(!viewModel.TransitionButtonText.IsEmpty());
            _transitButtonText.SetText(viewModel.TransitionButtonText.Value);

            // バナー
            _rawBannerComponent.IsVisible = !viewModel.BannerUrl.IsEmpty();
            if (_rawBannerComponent.IsVisible)
            {
                _rawBannerComponent.SetupDownloadBanner(viewModel.BannerUrl);
            }
        }
    }
}
