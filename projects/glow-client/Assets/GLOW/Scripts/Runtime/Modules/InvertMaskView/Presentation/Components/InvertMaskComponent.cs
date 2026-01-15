using System;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.InvertMaskView.Presentation.ViewModel;
using SoftMasking;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.InvertMaskView.Presentation.View
{
    public class InvertMaskComponent : UIObject
    {
        const float FadeDuration = 0.3f;

        [SerializeField] GameObject _grayOutObject;
        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] SoftMask _grayOutMask;
        [SerializeField] RectTransform _invertMaskRectTransform;
        [SerializeField] Button _grayOutButton;
        [SerializeField] Button _invertMaskButton;

        public RectTransform GetParentCanvasRectTransform()
        {
            return _invertMaskRectTransform.GetComponentInParent<Canvas>().GetComponent<RectTransform>();
        }

        InvertMaskViewModel _viewModel;
        Action _action;

        public void FadeInGrayOut(Action completedAction)
        {
            Hidden = false;
            _canvasGroup.alpha = 0f;
            _canvasGroup
                .DOFade(1f, FadeDuration)
                .OnComplete(() =>
                {
                    completedAction?.Invoke();
                })
                .SetLink(gameObject)
                .Play();
        }

        public void FadeOutGrayOut(Action completedAction)
        {
            _canvasGroup
                .DOFade(0f, FadeDuration)
                .OnComplete(() =>
                {
                    completedAction?.Invoke();
                    Hidden = true;
                })
                .SetLink(gameObject)
                .Play();
        }

        public void Setup(InvertMaskViewModel viewModel)
        {
            _grayOutButton.onClick.RemoveAllListeners();
            _invertMaskButton.onClick.RemoveAllListeners();

            _viewModel = viewModel;
            _grayOutButton.onClick.AddListener(() =>
            {
                if(_viewModel.AllowTapOnlyInvertMaskedAreaFlag) return;

                _action?.Invoke();
            });

            _invertMaskButton.onClick.AddListener(() =>
            {
                _action?.Invoke();
            });
        }

        public void ShowGrayOut()
        {
            Hidden = false;
            _canvasGroup.alpha = 1;
        }

        public void HideGrayOut()
        {
            Hidden = true;
        }

        public void SetTappedAction(Action action)
        {
            _action = action;
        }

        public void ShowInvertMask()
        {
            Hidden = false;
            _grayOutObject.SetActive(true);

            _invertMaskRectTransform.sizeDelta = new Vector2( _viewModel.InvertMaskSize.Width, _viewModel.InvertMaskSize.Height);
            _invertMaskRectTransform.localPosition = new Vector2(_viewModel.InvertMaskPosition.X, _viewModel.InvertMaskPosition.Y);

            _grayOutMask.invertMask = true;
            _grayOutMask.separateMask = _invertMaskRectTransform;
        }

        public void HideInvertMask()
        {
            Hidden = true;
        }

    }
}
