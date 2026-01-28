using System;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using GLOW.Scenes.Title.Presentations.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Title.Presentations.Views
{
    public sealed class TitleView : UIView
    {
        [SerializeField] UIText _applicationVersionText;
        [SerializeField] string _applicationVersionTextFormat = "Version {0}";
        [SerializeField] UIText _myIdText;
        [SerializeField] UIText _progressText;
        [SerializeField] UIImage _progressSliderImage;
        [SerializeField] UIText _loginPhaseText;
        [SerializeField] TitleTouchLayer _titleTouchLayer;
        [SerializeField] UIObject _loadingRoot;
        [SerializeField] UIObject _endLoadingRoot;
        [SerializeField] UIObject _menuButton;
        [SerializeField] Animator _animator;
        [SerializeField] UIImage _menuButtonBadgeImage;

        public Action OnTouch { get; set; }

        public AlreadyReadAnnouncementFlag AlreadyReadAnnouncementFlag => new(_menuButtonBadgeImage.Hidden);
        
        const string TitleInAnimationName = "Title-in";

        public void PlayInAnimation()
        {
            _animator.Play(TitleInAnimationName);
        }
        
        public void SetApplicationVersion(string version)
        {
            if (string.IsNullOrEmpty(_applicationVersionTextFormat))
            {
                _applicationVersionText.SetText(version);
                return;
            }

            _applicationVersionText.SetText(_applicationVersionTextFormat, version);
        }

        public void SetUserMyIdVisible(bool visible)
        {
            _myIdText.Hidden = !visible;
        }

        public void SetUserMyIdText(UserMyId id)
        {
            _myIdText.SetText(ZString.Format("ID : {0}", id.ToString()));
        }

        public void SetProgress(float progress)
        {
            _progressSliderImage.Image.fillAmount = progress;
            _progressText.SetText("{0}%", (int)(progress * 100));
        }

        public void SetLoginPhase(string phase)
        {
            _loginPhaseText.SetText(phase);
        }

        public void EndLoading()
        {
            _loadingRoot.Hidden = true;
            _endLoadingRoot.Hidden = false;
            _menuButton.Hidden = false;

            _titleTouchLayer.OnTouch = () => OnTouch?.Invoke();
        }

        public void SetMenuButtonNotificationBadge(NotificationBadge badge)
        {
            _menuButtonBadgeImage.Hidden = !badge;
        }
    }
}
