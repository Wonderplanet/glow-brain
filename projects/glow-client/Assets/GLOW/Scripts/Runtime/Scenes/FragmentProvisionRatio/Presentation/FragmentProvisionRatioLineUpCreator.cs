using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.FragmentProvisionRatioLineUp;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation
{
    public class FragmentProvisionRatioLineUpCreator :
        IFragmentProvisionRatioLineUpViewDelegate,
        IFragmentProvisionRatioLineUpViewDataSource
    {
        readonly ProvisionRatioItemListViewModel _viewModel;
        Action<MasterDataId> _onItemTap;
        FragmentProvisionRatioLineUpView _cellView;
        public FragmentProvisionRatioLineUpCreator(
            FragmentProvisionRatioLineUpView cellView,
            ProvisionRatioItemListViewModel viewModel,
            Action<MasterDataId> onItemTap)
        {
            _viewModel = viewModel;
            _onItemTap = onItemTap;
            _cellView = cellView;
            _cellView.ViewDelegate = this;
            _cellView.DataSource = this;
        }

        public void Setup()
        {
            _cellView.SetUp(_viewModel.Items.Count, _viewModel.Rarity);
        }

        void IFragmentProvisionRatioLineUpViewDelegate.OnUpdateItem(int index, GameObject gameObject)
        {
            var model = _viewModel.Items[index];

            var cell = gameObject.GetComponent<FragmentProvisionRatioLineUpCell>();
            cell.WhiteBackGround.gameObject.SetActive(index % 2 == 0);
            cell.GrayBackGround.gameObject.SetActive(index % 2 != 0);

            cell.CharacterIconComponent.gameObject.SetActive(false);
            cell.ItemIconComponent.gameObject.SetActive(true);
            cell.IconButton.onClick.AddListener(() => _onItemTap?.Invoke(model.MstUnitId));
            cell.ItemIconComponent.Setup(
                model.ItemIconViewModel.ItemIconAssetPath,
                model.ItemIconViewModel.Rarity,
                model.ItemIconViewModel.Amount);

            cell.NameText.SetText(model.ItemName.Value);
            cell.Ratio.SetText(model.OutputRatio.ToShowText());
        }

        int IFragmentProvisionRatioLineUpViewDataSource.InstantiateItemCont =>_viewModel?.Items.Count ?? 0;
    }

}
