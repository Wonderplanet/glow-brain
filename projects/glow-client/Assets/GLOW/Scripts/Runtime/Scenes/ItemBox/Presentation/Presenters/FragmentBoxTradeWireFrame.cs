using System;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using UIKit;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Presenters
{
    public class FragmentBoxTradeWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        
        FragmentBoxTradeViewController _fragmentBoxTradeViewController;
        
        Action _onUserItemUpdated;
        
        public void ShowItemDetailView(MasterDataId itemId, UIViewController parentViewController)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
                    ResourceType.Item,
                    itemId,
                    PlayerResourceAmount.Empty,
                    parentViewController);
        }
        
        public void ShowFragmentBoxTradeView(
            FragmentBoxTradeViewController.Argument argument,
            Action onUserItemUpdated,
            UIViewController parentViewController)
        {
            // 選択したアイテムが空か、マイナスの場合は何もしない
            if (argument.FragmentItemModel.Amount.IsZero() || argument.FragmentItemModel.Amount.IsMinus())
            {
                ApplicationLog.LogError(
                    nameof(FragmentBoxTradeWireFrame),
                    ZString.Format(
                        "選択したアイテムの個数が0以下です。アイテムID: {0}", 
                        argument.FragmentItemModel.Id.ToString()));
                
                return;
            }
            
            var controller = ViewFactory.Create<FragmentBoxTradeViewController, FragmentBoxTradeViewController.Argument>(argument);
            _fragmentBoxTradeViewController = controller;
            _onUserItemUpdated = onUserItemUpdated;
            parentViewController.PresentModally(controller);
        }
        
        public void CloseFragmentBoxTradeView()
        {
            _fragmentBoxTradeViewController?.Dismiss();
            _fragmentBoxTradeViewController = null;
        }
        
        public void OnUserItemUpdated()
        {
            _onUserItemUpdated?.Invoke();
        }
    }
}