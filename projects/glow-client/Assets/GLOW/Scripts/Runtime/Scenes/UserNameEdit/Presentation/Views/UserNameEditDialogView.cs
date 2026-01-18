using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using System.Collections;
using TMPro;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UserNameEdit.Presentation.Views
{
    public class UserNameEditDialogView : UIView
    {
        [SerializeField] TMP_InputField _inputField;
        [SerializeField] Button _saveButton;
        [SerializeField] Button _closeButton;
        [SerializeField] UIText _inputTextCount;
        [SerializeField] UIText _inputMaxLength;
        [SerializeField] UIText _headerText;
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _messageText;
        [SerializeField] UIText _changeTimeMessageText;
        [SerializeField] UIText _attentionText;
        [SerializeField] UIObject _okButtonGrayOut;
        [SerializeField] Animator _okButtonAnimator;

        Vector2 _inputFieldAnchorPosition;
        Vector2 _inputFieldCaretAnchorPosition;
        TMP_SelectionCaret _inputFieldCaret;

        protected override void Awake()
        {
            base.Awake();

            _inputFieldAnchorPosition = _inputField.textComponent.rectTransform.anchoredPosition;
            StartCoroutine(GetInputFieldCaret());

            _inputField.onValueChanged.AddListener(text =>
            {
                _saveButton.interactable = !string.IsNullOrWhiteSpace(text);
                SetTextLength(text);
            });
            _inputField.onEndEdit.AddListener(text =>
            {
                _saveButton.interactable = !string.IsNullOrWhiteSpace(text);
                _inputField.text = UserName.Culling(text);
                SetTextLength(_inputField.text);

                // 文字数制限によりScript側から制御した場合、InputFieldの表示位置がずれることがあるため対応
                _inputField.textComponent.rectTransform.anchoredPosition = _inputFieldAnchorPosition;
                if (_inputFieldCaret == null) return;
                _inputFieldCaret.rectTransform.anchoredPosition = _inputFieldCaretAnchorPosition;
            });

        }

        IEnumerator GetInputFieldCaret()
        {
            yield return null;
            // TMP_InputFieldのtextViewportからCaretを取得する関係で生成後を待ちたい、1フレーム待機
            _inputFieldCaret = _inputField.textViewport.GetComponentInChildren<TMP_SelectionCaret>();
            if (_inputFieldCaret == null) yield break;
            _inputFieldCaretAnchorPosition = _inputFieldCaret.rectTransform.anchoredPosition;
        }

        public string InputText => _inputField.text;

        public void SetUserName(UserName userName)
        {
            _inputField.text = userName.Value;
            _inputMaxLength.SetText(UserName.MaxLength.ToString());
            SetTextLength(userName.Value);
            _saveButton.interactable = !string.IsNullOrWhiteSpace(userName.Value);
        }

        public void SetTitle(string message)
        {
            _titleText.SetText(message);
        }

        public void SetMessage(string message)
        {
            _changeTimeMessageText.Hidden = true;
            _messageText.Hidden = false;

            _messageText.SetText(message);
        }

        public void SetRemainingTimeSpan(RemainingTimeSpan remainingTimeSpan)
        {
            _changeTimeMessageText.Hidden = false;
            _messageText.Hidden = true;

            _changeTimeMessageText.SetText(TimeSpanFormatter.FormatRemaining(remainingTimeSpan));
        }

        public void SetOkButtonGrayOut()
        {
            _okButtonAnimator.enabled = false;
            _okButtonGrayOut.Hidden = false;
        }

        public void SetInputFieldGrayOut()
        {
            _inputField.readOnly = true;
        }

        public void SetHeaderText(string text)
        {
            _headerText.SetText(text);
        }

        public void HideTutorialButton()
        {
            _closeButton.gameObject.SetActive(false);
        }

        public void SetAttentionText(string message)
        {
            _attentionText.SetText(message);
        }

        void SetTextLength(string text)
        {
            _inputTextCount.SetText(text?.Length.ToString() ?? "0");
        }
    }
}
