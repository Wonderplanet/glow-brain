namespace GLOW.Core.Domain.Calculator
{
    public interface IUnitBaseStatusCalculator
    {
        decimal CalculateBaseStatus(decimal minStatus, decimal maxStatus, decimal currentLevel, decimal maxLevel, decimal exponent);
    }
}
