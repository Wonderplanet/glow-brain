using DG.Tweening;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace WPFramework.Presentation.Views
{
    public class NavigationBar : UIView
    {
        [SerializeField] Text _contextTitleText = null;
        [SerializeField] Text _titleText = null;
        [SerializeField] UIBarButtonItem _backButton = null;

        Text _currentText = null;

        public Button.ButtonClickedEvent BackButtonOnClickEvent =>
            _backButton == default ? default : _backButton.Button.onClick;

        public string ContextTitle
        {
            set
            {
                if (_contextTitleText)
                {
                    _contextTitleText.text = value;
                }
            }
        }

        public string Title
        {
            set
            {
                if (_currentText)
                {
                    _currentText.text = value;
                }
            }
        }

        public bool BackButtonActive
        {
            get => _backButton == default ? default : _backButton.Hidden;
            set
            {
                if (_backButton)
                {
                    _backButton.Hidden = !value;
                }
            }
        }

        protected override void Awake()
        {
            base.Awake();

            if (_titleText)
            {
                _titleText.gameObject.SetActive(false);
            }

            if (_currentText)
            {
                _currentText = InstantiateTitleText();
                _currentText.text = "";
            }
        }

        Text InstantiateTitleText()
        {
            if (!_titleText)
            {
                return default;
            }

            var t = Instantiate(_titleText, _titleText.transform.parent);
            t.transform.localPosition = _titleText.transform.localPosition;
            t.gameObject.SetActive(true);
            return t;
        }

        public void SetTitleWithAnimate(string title)
        {
            if (!_currentText)
            {
                return;
            }

            var disappearText = _currentText;
            _currentText = InstantiateTitleText();
            _currentText.text = title;

            var color = _titleText.color;
            color.a = 0;
            _currentText.color = color;

            DOTween.Sequence()
                .Append(DOTween.ToAlpha(() => disappearText.color, c => disappearText.color = c, 0f, 0.2f))
                .Append(DOTween.ToAlpha(() => _currentText.color, c => _currentText.color = c, 1f, 0.2f))
                .OnComplete(() => Destroy(disappearText.gameObject))
                .Play();
        }
    }
}
