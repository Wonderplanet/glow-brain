using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstUnitDataTranslator
    {
        public static MstUnitLevelUpModel TranslateLevelUp(MstUnitLevelUpData data)
        {
            return new MstUnitLevelUpModel(
                new UnitLevel(data.Level),
                data.UnitLabel,
                new Coin(data.RequiredCoin));
        }

        public static MstUnitRankUpModel TranslateRankUp(MstUnitRankUpData data)
        {
            return new MstUnitRankUpModel(
                new UnitRank(data.Rank),
                data.UnitLabel,
                new ItemAmount(data.Amount),
                new UnitLevel(data.RequireLevel),
                new ItemAmount(data.SrMemoryFragmentAmount),
                new ItemAmount(data.SsrMemoryFragmentAmount),
                new ItemAmount(data.UrMemoryFragmentAmount));
        }

        public static MstUnitRankCoefficientModel TranslateRankCoefficient(MstUnitRankCoefficientData data)
        {
            return new MstUnitRankCoefficientModel(
                new UnitRank(data.Rank),
                new UnitRankCoefficient(data.Coefficient),
                new UnitRankCoefficient(data.SpecialUnitCoefficient)
            );
        }

        public static MstUnitGradeUpModel TranslateGradeUp(MstUnitGradeUpData data)
        {
            return new MstUnitGradeUpModel(
                new UnitGrade(data.GradeLevel),
                data.UnitLabel,
                new ItemAmount(data.RequireAmount)
            );
        }

        public static MstUnitGradeCoefficientModel TranslateGradeCoefficient(MstUnitGradeCoefficientData data)
        {
            return new MstUnitGradeCoefficientModel(
                new UnitGrade(data.GradeLevel),
                data.UnitLabel,
                new UnitGradeCoefficient(data.Coefficient)
            );
        }
    }
}
