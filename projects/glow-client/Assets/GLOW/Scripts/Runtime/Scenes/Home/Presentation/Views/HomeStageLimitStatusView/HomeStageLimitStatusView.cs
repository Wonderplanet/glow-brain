using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView
{
    public sealed class HomeStageLimitStatusView : UIView
    {
        [SerializeField] UIText _partyName;

        [Header("編成条件")]
        [SerializeField] GameObject _seriesLogoImageArea;
        [SerializeField] GameObject _titleCellParent;
        [SerializeField] InGameSpecialRuleTitleCell _titleCellPrefab;
        [SerializeField] GameObject _unitRarityArea;
        [SerializeField] List<GameObject> _unitRarities;
        [SerializeField] GameObject _unitRoleArea;
        [SerializeField] List<GameObject> _unitRoles;
        [SerializeField] GameObject _unitCountArea;
        [SerializeField] UIText _unitCountText;
        [SerializeField] GameObject _unitAttackRangeArea;
        [SerializeField] UIText _unitAttackRangeText;

        public void SetupEmpty()
        {
            _partyName.SetText(string.Empty);
            _seriesLogoImageArea.SetActive(false);
            _unitRarityArea.SetActive(false);
            _unitRoleArea.SetActive(false);
            _unitCountArea.SetActive(false);
            _unitAttackRangeArea.SetActive(false);
        }

        public void SetPartyName(PartyName partyName)
        {
            _partyName.SetText(partyName.Value);
        }

        public void SetupSeriesLogos(IReadOnlyList<SeriesLogoImagePath> seriesLogoImagePathList)
        {
            _seriesLogoImageArea.SetActive(seriesLogoImagePathList.Count > 0);

            foreach (var seriesLogoImagePath in seriesLogoImagePathList)
            {
                var titleCell = Instantiate(_titleCellPrefab, _titleCellParent.transform);
                titleCell.Setup(seriesLogoImagePath);
            }
        }

        public void SetupUnitRarity(IReadOnlyList<Rarity> rarities)
        {
            _unitRarityArea.SetActive(rarities.Count > 0);
            _unitRarities.ForEach(rarity => rarity.SetActive(false));
            foreach (var rarity in rarities)
            {
                var index = (int)rarity;
                if (index < 0 || index >= _unitRarities.Count) continue;
                _unitRarities[index].SetActive(true);
            }
        }

        public void SetupUnitRoleType(IReadOnlyList<CharacterUnitRoleType> roleTypes)
        {
            _unitRoleArea.SetActive(roleTypes.Count > 0);
            _unitRoles.ForEach(role => role.SetActive(false));
            foreach (var role in roleTypes)
            {
                if (role == CharacterUnitRoleType.None) continue;

                var index = (int)role - 1;
                if (index < 0 || index >= _unitRoles.Count) continue;
                _unitRoles[index].SetActive(true);
            }
        }

        public void SetupUnitCount(PartyUnitNum unitNum)
        {
            _unitCountArea.SetActive(!unitNum.IsZeroOrLess());
            _unitCountText.SetText(unitNum.ToStringForSpecialRule());
        }

        public void SetupAttackRange(IReadOnlyList<CharacterAttackRangeType> unitAttackRangeTypes)
        {
            _unitAttackRangeArea.SetActive(unitAttackRangeTypes.Count > 0);
            var stringValues = new List<string>();
            for (var i = 0; i < unitAttackRangeTypes.Count; ++i)
            {
                var attackRange = unitAttackRangeTypes[i];
                stringValues.Add(attackRange.ToLocalizeString());

                if (i >= unitAttackRangeTypes.Count - 1) continue;

                stringValues.Add(" / ");
            }
            _unitAttackRangeText.SetText(ZString.Concat(stringValues));
        }
    }
}
