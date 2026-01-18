using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ProductName(ObscuredString Value)
    {
        public static ProductName Empty { get; } = new (string.Empty);

        public static ProductName Coin { get; } = new ("コイン");
        public static ProductName FreeDiamond { get; } = new ("無償プリズム");
        public static ProductName PaidDiamond { get; } = new ("有償プリズム");
        public static ProductName Exp { get; } = new ProductName("経験値");

        public static ProductName FromTypeAndName(
            ResourceType type,
            ItemName itemName,
            CharacterName characterName,
            ProductResourceAmount amount)
        {
            switch (type)
            {
                case ResourceType.FreeDiamond:
                {
                    return new ProductName("無償プリズム");
                }
                case ResourceType.PaidDiamond:
                {
                    return new ProductName("有償プリズム");
                }
                case ResourceType.Coin:
                {
                    return new ProductName("コイン");
                }
                case ResourceType.IdleCoin:
                {
                    return new ProductName(ZString.Format("{0}時間分探索コイン", amount.ToString()));
                }
                case ResourceType.Item:
                {
                    return new ProductName(itemName.Value);
                }
                case ResourceType.Unit:
                {
                    return new ProductName(characterName.Value);
                }
                default:
                    return new ProductName("");
            }
        }

        public static ProductName WithProductResourceAmount(ProductName name, ProductResourceAmount amount)
        {
            return new ProductName($"{name.Value}{amount.ToStringWithMultiplicationAndSeparate()}");
        }

        public static ProductName FromResourceTypeWithProductResourceAmount(ResourceType type, ProductResourceAmount amount)
        {
            switch (type)
            {
                case ResourceType.FreeDiamond:
                case ResourceType.PaidDiamond:
                {
                    return new ProductName($"プリズム {amount.ToStringWithSeparate()}個");
                }
                case ResourceType.Coin:
                {
                    return new ProductName($"コイン {amount.ToStringWithMultiplicationAndSeparate()}");
                }
                case ResourceType.IdleCoin:
                {
                    return new ProductName($"{amount}時間分探索コイン");
                }
                default:
                    return new ProductName("");
            }
        }
        
        public override string ToString()
        {
            return Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
