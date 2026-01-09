using Cysharp.Threading.Tasks;
using UIKit;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Modules.MessageView.Presentation
{
    public class MessageViewController : UIViewController<MessageView>
    {
        const int SortingOrder = 3000;
        const int CloseDelayMilliseconds = 40;

        public static MessageViewController WithTitleAndMessage(
            string title,
            string message,
            string attentionMessage = null,
            string prefabName = null)
        {
            var controller = new MessageViewController();
            if (prefabName != null) controller.PrefabName = prefabName;

            controller.SetTitle(title);
            controller.SetDescriptionMessage(message);
            controller.SetAttentionMessage(attentionMessage);

            return controller;
        }

        public void AddAction(UIMessageAction action)
        {
            var clickEvent = ActualView.AddActionButton(action.Title, action.Style);

            clickEvent.AddListenerAsExclusive(() =>
            {
                OnActionSelect(action);
            });
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            
            SetUpSortingOrder();
            
            if (animated && View.IsAnimateAvailable) View.Animate("appear");
        }

        void SetUpSortingOrder()
        {
            var canvas = ActualView.GetComponentInParent<Canvas>(includeInactive: true);
            if (canvas != null)
            {
                canvas.sortingOrder = SortingOrder;
            }
        }

        void OnActionSelect(UIMessageAction action)
        {
            DoAsync.Invoke(View, async cancellationToken =>
            {
                await UniTask.Delay(CloseDelayMilliseconds, cancellationToken: cancellationToken);

                action.Invoke();
                Dismiss();
            });
        }

        void SetTitle(string title)
        {
            ActualView.TitleText.SetText(title);
            ActualView.TitleText.Hidden = string.IsNullOrEmpty(title);
        }

        void SetDescriptionMessage(string descriptionMessage)
        {
            ActualView.DescriptionMessageText.SetText(descriptionMessage);
            ActualView.DescriptionMessageText.Hidden = string.IsNullOrEmpty(descriptionMessage);
        }

        void SetAttentionMessage(string attentionMessage)
        {
            ActualView.AttentionMessageText.SetText(attentionMessage);
            ActualView.AttentionMessageText.Hidden = string.IsNullOrEmpty(attentionMessage);
        }
    }
}
