using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation.FragmentProvisionRatioLineUp
{
    public interface IFragmentProvisionRatioLineUpViewDelegate
    {
        void OnUpdateItem(int index, GameObject gameObject);
    }

    public interface IFragmentProvisionRatioLineUpViewDataSource
    {
        int InstantiateItemCont { get; }
    }

    public interface IFragmentProvisionRatioLineUpView
    {
        void SetUp(int count, Rarity rarity);
    }

    public class FragmentProvisionRatioLineUpView : MonoBehaviour, IFragmentProvisionRatioLineUpView
    {
        [SerializeField] UIText _itemCount;
        [SerializeField] RectTransform _contentRect;
        [SerializeField] RectTransform itemPrototype;

        public IFragmentProvisionRatioLineUpViewDelegate ViewDelegate { get; set; }
        public IFragmentProvisionRatioLineUpViewDataSource DataSource { get; set; }

        readonly LinkedList<RectTransform> _itemList = new();
        IReadOnlyList<ProvisionRatioItemViewModel> _itemModels;

        const string CountFormat = "{0}(全{1}種)";//例：UR(全１種)

        public void SetUp(int count, Rarity rarity)
        {
            // poolするプレハブ作成
            Build();

            _itemCount.SetText(CountFormat, rarity.ToString(), count);
        }

        void Build()
        {
            itemPrototype.gameObject.SetActive(false);

            for (int i = 0; i < DataSource.InstantiateItemCont; i++)
            {
                var item = Instantiate(itemPrototype.gameObject).GetComponent<RectTransform>();
                item.SetParent(transform, false);
                item.name = i.ToString();
                _itemList.AddLast(item);

                item.gameObject.SetActive(true);

                ViewDelegate.OnUpdateItem(i, item.gameObject);
            }
        }
    }
}
