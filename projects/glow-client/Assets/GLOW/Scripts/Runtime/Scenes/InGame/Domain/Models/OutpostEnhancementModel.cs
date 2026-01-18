using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Extensions;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record OutpostEnhancementModel(IReadOnlyList<OutpostEnhancementElement> EnhancementElements)
    {
        public static OutpostEnhancementModel Empty { get; } = new (new List<OutpostEnhancementElement>());
        
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public OutpostEnhanceValue GetEnhancementValue(OutpostEnhancementType type)
        {
            var element = EnhancementElements.FirstOrDefault(
                x => x.Type == type,
                OutpostEnhancementElement.Empty);

            return element.Value;
        }

        public virtual bool Equals(OutpostEnhancementModel other)
        {
            if (ReferenceEquals(this, other)) return true;
            if (other is null) return false;
            
            if (EnhancementElements == null && other.EnhancementElements == null) return true;
            if ((EnhancementElements == null) ^ (other.EnhancementElements == null)) return false;
            if (EnhancementElements != null && other.EnhancementElements != null)
            {
                if (!EnhancementElements.SequenceEqual(other.EnhancementElements)) return false;

            }
            return true;
        }

        public override int GetHashCode()
        {
            HashCode hash = new();
            if (EnhancementElements != null)
            {
                foreach (var element in EnhancementElements)
                {
                    hash.Add(element);
                }
            }
            return hash.ToHashCode();
        }
    }
}
