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
    public class NoticeSimpleBannerView : UIView
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

            // バナー(簡易バナーの場合、ツール上必ず設定される)
            _rawBannerComponent.IsVisible = true;
            _rawBannerComponent.SetupDownloadBanner(viewModel.BannerUrl);
        }
    }
}