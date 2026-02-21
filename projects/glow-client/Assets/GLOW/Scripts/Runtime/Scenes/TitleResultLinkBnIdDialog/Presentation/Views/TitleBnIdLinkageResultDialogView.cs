using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Views
{
    public class TitleBnIdLinkageResultDialogView : UIView
    {
        [SerializeField] UIText _titleText;
        [SerializeField] UIText _messageText;
        [SerializeField] UIObject _dataRoot;
        [SerializeField] UIObject _dateTitleRoot;
        [SerializeField] UIText _dateTitleText;
        [SerializeField] UIObject _myIdRoot;
        [SerializeField] UIText _myIdText;
        [SerializeField] UIObject _userNameRoot;
        [SerializeField] UIText _userNameText;
        [SerializeField] UIObject _levelRoot;
        [SerializeField] UIText _levelText;
        [SerializeField] UIObject _attentionMessageRoot;
        [SerializeField] UIText _attentionMessageText;
        [SerializeField] UITextButton _leftButton;
        [SerializeField] UITextButton _rightButton;

        public void SetTitleText(TitleBnIdLinkageResultTitle title)
        {
            _titleText.SetText(title.Value);
        }

        public void SetMessageText(TitleBnIdLinkageResultMessage message)
        {
            _messageText.SetText(message.Value);
        }

        public void SetDateTitleText(TitleBnIdLinkageResultDateTitle dateTitle)
        {
            _dateTitleRoot.Hidden = dateTitle.IsEmpty();
            _dateTitleText.SetText(dateTitle.Value);
        }

        public void SetMyIdText(UserMyId myId)
        {
            _myIdRoot.Hidden = myId.IsEmpty();
            _myIdText.SetText(myId.Value);
        }

        public void SetUserNameText(UserName userName)
        {
            _userNameRoot.Hidden = userName.IsEmpty();
            _userNameText.SetText(userName.Value);
        }

        public void SetLevelText(UserLevel level)
        {
            _levelRoot.Hidden = level.IsEmpty();
            _levelText.SetText(level.ToStringAmount());
        }

        public void SetDataRootEnabled(
            TitleBnIdLinkageResultDateTitle dateTitle,
            UserMyId myId,
            UserName userName,
            UserLevel level)
        {
            // 全ての情報がない場合はRootを非表示にする
            if (dateTitle.IsEmpty() &&
                myId.IsEmpty() &&
                userName.IsEmpty() &&
                level.IsEmpty())
            {
                _dataRoot.IsVisible = false;
            }
        }

        public void SetAttentionMessageText(TitleBnIdLinkageResultAttentionMessage attentionMessage)
        {
            _attentionMessageRoot.Hidden = attentionMessage.IsEmpty();
            _attentionMessageText.SetText(attentionMessage.Value);
        }

        public void SetLeftButtonText(TitleBnIdLinkageResultLeftButtonTitle leftButtonText)
        {
            _leftButton.gameObject.SetActive(!leftButtonText.IsEmpty());
            _leftButton.TitleText.SetText(leftButtonText.Value);
        }

        public void SetRightButtonText(TitleBnIdLinkageResultRightButtonTitle rightButtonText)
        {
            _rightButton.gameObject.SetActive(!rightButtonText.IsEmpty());
            _rightButton.TitleText.SetText(rightButtonText.Value);
        }
    }
}
