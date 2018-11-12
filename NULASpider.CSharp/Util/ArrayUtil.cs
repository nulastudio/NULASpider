using System;
using Pchp.Core;
using Pchp.Library;
using System.Collections.Generic;

[assembly: PhpExtension]
public static class ArrayUtil
{
    public static bool is_indexed_array(Context ctx, PhpArray arr)
    {
        PhpCallback callback = PhpCallback.Create("is_string", default(RuntimeTypeHandle));
        PhpArray keys = Arrays.array_keys(arr);
        return Arrays.array_filter(ctx, keys, callback).Count == 0;
    }
    public static bool is_continuous_indexed_array(Context ctx, PhpArray arr)
    {
        PhpArray keys = Arrays.array_keys(arr);
        bool isIncomparable;
        return PhpArray.CompareArrays(keys, Arrays.array_keys(keys), PhpArrayKeysComparer.Default, out isIncomparable) == 0;
    }
    public static bool is_assoc_array(Context ctx, PhpArray arr)
    {
        PhpCallback callback = PhpCallback.Create("is_string", default(RuntimeTypeHandle));
        PhpArray keys = Arrays.array_keys(arr);
        // mixed array is one kind of assoc array
        // return Arrays.array_filter(ctx, keys, callback).Count == arr.Count;
        return Arrays.array_filter(ctx, keys, callback).Count != 0;
    }
    public static bool is_mixed_array(Context ctx, PhpArray arr)
    {
        PhpCallback callback = PhpCallback.Create("is_string", default(RuntimeTypeHandle));
        PhpArray keys = Arrays.array_keys(arr);
        int count = Arrays.array_filter(ctx, keys, callback).Count;
        return count != 0 && count != arr.Count;
    }
    public static dynamic array2collection(Context ctx, PhpArray arr)
    {
        dynamic dic;
        bool isList = is_continuous_indexed_array(ctx, arr);
        if (isList)
        {
            dic = new List<object> {};
        } else {
            dic = new Dictionary<string, object> {};
        }
        foreach (KeyValuePair<IntStringKey, PhpValue> item in arr)
        {
            object val;
            if (item.Value.IsArray)
            {
                val = array2collection(ctx, item.Value.ToArray());
            } else {
                val = item.Value.IsNull ? null : item.Value.ToClr();
            }
            if (isList)
            {
                dic.Add(val);
            } else {
                dic.Add(item.Key.ToString(), val);
            }
        }
        return dic;
    }
    public static PhpArray collection2array(Context ctx, Dictionary<string, object> dic)
    {
        PhpArray arr = PhpArray.NewEmpty();
        foreach (KeyValuePair<string, object> item in dic)
        {
            if (item.Value is List<object>)
            {
                    arr.Add(item.Key, collection2array(ctx, item.Value as List<object>));
            } else if (item.Value is Dictionary<string, object>)
            {
                arr.Add(item.Key, collection2array(ctx, item.Value as Dictionary<string, object>));
            } else {
                int intKey = 0;
                dynamic key;
                if (int.TryParse(item.Key, out intKey))
                {
                    key = intKey;
                } else {
                    key = item.Key;
                }
                arr.Add(new IntStringKey(key), PhpValue.FromClr(item.Value));
            }
        }
        return arr;
    }
    public static PhpArray collection2array(Context ctx, List<object> dic)
    {
        PhpArray arr = PhpArray.NewEmpty();
        foreach (object item in dic)
        {
            if (item is List<object>)
            {
                arr.Add(collection2array(ctx, item as List<object>));
            } else if (item is Dictionary<string, object>)
            {
                arr.Add(collection2array(ctx, item as Dictionary<string, object>));
            } else {
                arr.Add(PhpValue.FromClr(item));
            }
        }
        return arr;
    }
}