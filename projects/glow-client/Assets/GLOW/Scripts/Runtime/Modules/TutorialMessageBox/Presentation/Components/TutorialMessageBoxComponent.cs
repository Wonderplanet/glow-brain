using System;
using System.Collections;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.TutorialMessageBox.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.TutorialMessageBox.Presentation.Components
{
    public class TutorialMessageBoxComponent : UIObject
    {
        const string ShowAnim = "TutorialMessage-in";
        const string HideAnim = "TutorialMessage-out";
        [SerializeField] UIText _text;
        [SerializeField] Animator _animator;
        [SerializeField] Button _button;
        string _textValue;
        bool _isActiveButton;

        public void Setup(
            TutorialMessageBoxViewModel viewModel,
            AllowTapOnlyInvertMaskedAreaFlag allowTapOnlyInvertMaskedAreaFlag,
            Action onCompletedAction)
        {
            // ボタンのリセット
            _button.onClick.RemoveAllListeners();

            _textValue = viewModel.Text.Value;
            _text.SetText(viewModel.Text.Value);
            RectTransform.anchoredPosition = new Vector2(RectTransform.anchoredPosition.x, viewModel.PositionY.Value);

            if (allowTapOnlyInvertMaskedAreaFlag)
            {
                _isActiveButton = false;
                return;
            }

            _isActiveButton = true;
            _button.onClick.AddListener(() =>
            {
                // 文字送りのスキップが終わっていなければスキップ処理
                if (_text.Text != _textValue)
                {
                    SkipDisplaySentence();
                    return;
                }

                // 表示完了していればCompletedActionを実行
                onCompletedAction?.Invoke();
            });
        }

        public void Show()
        {
            Hidden = false;
            _button.gameObject.SetActive(_isActiveButton);

            // TODO:テキストが既に入っている場合はアニメーションを再生しない
            _animator.Play(ShowAnim, 0, 0);

            // TODO:テキストボックス表示後に文字送り開始
        }

        public void UpdateMessageBoxText()
        {
            _text.SetText(_textValue);
        }

        //
        public void SkipDisplaySentence()
        {
            _animator.Play(ShowAnim, 0, 1);
            // 文字送りのスキップ
            _text.SetText(_textValue);
        }

        public void Hide()
        {
            _animator.Play(HideAnim, 0, 0);
            _button.gameObject.SetActive(false);
            Hidden = true;
        }

        public void Hide(Action action)
        {
            _animator.Play(HideAnim, 0, 0);
            // 0.3秒コルーチンで待つ
            StartCoroutine(WaitForHide(action));
        }

        IEnumerator WaitForHide(Action action)
        {
            yield return new WaitForSeconds(0.2f);
            action?.Invoke();
            _button.gameObject.SetActive(false);
            Hidden = true;
        }
    }
}
