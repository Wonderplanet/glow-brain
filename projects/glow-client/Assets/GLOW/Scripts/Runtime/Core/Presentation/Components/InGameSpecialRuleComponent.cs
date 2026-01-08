using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class InGameSpecialRuleComponent : UIComponent
    {
        [SerializeField] GameObject _seriesLogoImageArea;

        [Header("編成条件")]
        [SerializeField] UIObject _sbTitle1;
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
        [SerializeField] UIObject _noFormationRule;

        [Header("その他")]
        [SerializeField] UIObject _subTitle2;
        [SerializeField] GameObject _speedAttackArea;
        [SerializeField] GameObject _timeLimitArea;
        [SerializeField] UIText _timeLimitText;
        [SerializeField] GameObject _defenseTargetArea;
        [SerializeField] UIText _defenseTargetText;
        [SerializeField] GameObject _enemyDestructionArea;
        [SerializeField] UIText _enemyDestructionText;
        [SerializeField] GameObject _specificEnemyDestructionArea;
        [SerializeField] UIText _specificEnemyDestructionText;
        [SerializeField] GameObject _startOutpostHpArea;
        [SerializeField] UIText _startOutpostHpText;
        [SerializeField] GameObject _enemyOutpostDamageInvalidationArea;
        [SerializeField] GameObject _noContinueArea;
        [SerializeField] GameObject _unitSelectHeaderComment;
        [SerializeField] GameObject _defaultHeaderComment;
        [SerializeField] GameObject _defaultHeaderCommentWithoutFormationRule;
        [SerializeField] UIObject _noOtherFormationRule;

        public void SetupEmpty()
        {
            _seriesLogoImageArea.SetActive(false);
            _sbTitle1.IsVisible = false;
            _unitRarityArea.SetActive(false);
            _unitRoleArea.SetActive(false);
            _unitCountArea.SetActive(false);
            _unitAttackRangeArea.SetActive(false);
            _subTitle2.IsVisible = false;
            _speedAttackArea.SetActive(false);
            _defenseTargetArea.SetActive(false);
            _enemyDestructionArea.SetActive(false);
            _specificEnemyDestructionArea.SetActive(false);
            _startOutpostHpArea.SetActive(false);
            _enemyOutpostDamageInvalidationArea.SetActive(false);
            _noContinueArea.SetActive(false);
        }

        public void SetupSeriesLogos(List<SeriesLogoImagePath> seriesLogoImagePathList)
        {
            _seriesLogoImageArea.SetActive(seriesLogoImagePathList.Count > 0);

            foreach (var seriesLogoImagePath in seriesLogoImagePathList)
            {
                var titleCell = Instantiate(_titleCellPrefab, _titleCellParent.transform);
                titleCell.Setup(seriesLogoImagePath);
            }
        }

        public void SetupUnitRarity(List<Rarity> rarities)
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

        public void SetupUnitRoleType(List<CharacterUnitRoleType> roleTypes)
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

        public void SetupUnitCount(InGameSpecialRuleUnitAmount unitAmount)
        {
            _unitCountArea.SetActive(!unitAmount.IsZeroOrLess());
            _unitCountText.SetText(unitAmount.ToStringForSpecialRule());
        }

        public void SetupAttackRange(List<CharacterAttackRangeType> unitAttackRangeTypes)
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

        public void SetupSpeedAttack(InGameSpecialRuleSpeedAttackFlag isSpeedAttack)
        {
            _speedAttackArea.SetActive(isSpeedAttack);
        }

        public void SetupTimeLimit(InGameSpecialRuleTimeLimit timeLimit)
        {
            var isEmpty = timeLimit.IsEmpty();
            _timeLimitArea.SetActive(!isEmpty);
            _timeLimitText.SetText(timeLimit.IsDefeat ? "制限時間が0で敗北" : "制限時間が0で勝利");
        }

        public void SetupDefenseTarget(InGameSpecialRuleDefenseTargetFlag isDefenseTarget)
        {
            _defenseTargetArea.SetActive(isDefenseTarget);
            _defenseTargetText.SetText("ターゲットのHPが0になると敗北");
        }

        public void SetupEnemyDestruction(DefeatEnemyCount count, InGameSpecialRuleEnemyDestructionFlag isEnemyDestruction)
        {
            _enemyDestructionArea.SetActive(isEnemyDestruction);
            _enemyDestructionText.SetText("敵を{0}体撃破すると勝利", count.Value);
        }

        public void SetupSpecificEnemyDestruction(
            CharacterName enemyName,
            DefeatEnemyCount count,
            InGameSpecialRuleSpecificEnemyDestructionFlag isSpecificEnemyDestruction)
        {
            _specificEnemyDestructionArea.SetActive(isSpecificEnemyDestruction);
            _specificEnemyDestructionText.SetText("{0}を{1}体撃破すると勝利", enemyName.Value, count.Value);
        }

        public void SetupStartOutpost(InGameSpecialRuleStartOutpostHp startOutpostHp, InGameSpecialRuleStartOutpostHpFlag isStartOutpostHp)
        {
            _startOutpostHpArea.SetActive(isStartOutpostHp);
            _startOutpostHpText.SetText("HP{0}でバトル開始", startOutpostHp.Value);
        }

        public void SetupEnemyOutpostDamageInvalidation(InGameSpecialRuleEnemyOutpostDamageInvalidationFlag isEnemyOutpostDamageInvalidation)
        {
            _enemyOutpostDamageInvalidationArea.SetActive(isEnemyOutpostDamageInvalidation);
        }

        public void SetupNoContinue(InGameSpecialRuleNoContinueFlag isNoContinue)
        {
            _noContinueArea.SetActive(isNoContinue);
        }

        public void SetupHeaderComment(
            InGameSpecialRuleFromUnitSelectFlag isFromUnitSelect,
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule)
        {
            _unitSelectHeaderComment.SetActive(isFromUnitSelect);
            _defaultHeaderComment.SetActive(!isFromUnitSelect && existsFormationRule);
            _defaultHeaderCommentWithoutFormationRule.SetActive(!isFromUnitSelect && !existsFormationRule);
        }

        public void SetUpSpecialRuleHeader(
            InGameSpecialRuleExistFormationRuleFlag existsFormationRule,
            InGameSpecialRuleExistOtherRuleFlag existsOtherRule)
        {
            if (!existsFormationRule && !existsOtherRule)
            {
                _sbTitle1.IsVisible = false;
                _subTitle2.IsVisible = false;
                _noFormationRule.IsVisible = false;
                _noOtherFormationRule.IsVisible = false;
                return;
            }
            _sbTitle1.IsVisible = true;
            _subTitle2.IsVisible = true;

            _noFormationRule.IsVisible = !existsFormationRule;
            _noOtherFormationRule.IsVisible = !existsOtherRule;
        }
    }
}
